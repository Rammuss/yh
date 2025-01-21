CREATE OR REPLACE FUNCTION public.generar_venta(
    p_cliente_id integer,
    p_forma_pago character varying,
    p_cuotas integer,
    p_detalles jsonb,
    p_fecha timestamp without time zone,
    p_metodo_pago character varying DEFAULT NULL::character varying,
    p_nota_credito_id integer DEFAULT NULL::integer,
    p_solicitud_id integer DEFAULT NULL::integer,
    p_monto_total_servicios numeric DEFAULT NULL::numeric)
    RETURNS integer
    LANGUAGE 'plpgsql'
    COST 100
    VOLATILE PARALLEL UNSAFE
AS $BODY$
DECLARE
    v_numero_factura INTEGER;
    v_timbrado VARCHAR(15);
    v_rango_inicio INTEGER;
    v_rango_fin INTEGER;
    v_id_rango INTEGER;
    v_fecha_inicio TIMESTAMP;
    v_fecha_fin TIMESTAMP;
    v_monto_total DECIMAL(10, 2);
    v_monto_por_cuota DECIMAL(10, 2);
    v_fecha_vencimiento DATE;
    v_monto_nc_aplicado DECIMAL(10, 2) := 0;
    i INTEGER;
    v_venta_id INTEGER; -- Variable para almacenar el ID de la venta
BEGIN
    -- Bloquear el rango de facturas activo para evitar conflictos
    SELECT id, rango_inicio, rango_fin, timbrado, actual, fecha_inicio, fecha_fin
    INTO v_id_rango, v_rango_inicio, v_rango_fin, v_timbrado, v_numero_factura, v_fecha_inicio, v_fecha_fin
    FROM rango_facturas
    WHERE activo = true FOR UPDATE;

    -- Verificar que existe un rango activo
    IF NOT FOUND THEN
        RAISE EXCEPTION 'No hay un rango de facturas activo';
    END IF;

    -- Verificar que la fecha esté dentro del rango permitido
    IF p_fecha < v_fecha_inicio OR p_fecha > v_fecha_fin THEN
        RAISE EXCEPTION 'La fecha de la factura (%), no está dentro del rango válido (% - %)', p_fecha, v_fecha_inicio, v_fecha_fin;
    END IF;

    -- Verificar que el número de factura no haya superado el rango disponible
    IF v_numero_factura >= v_rango_fin THEN
        RAISE EXCEPTION 'El rango de facturas ha sido agotado';
    END IF;

    -- Asignar el siguiente número de factura y actualizar el rango
    v_numero_factura := v_numero_factura + 1;
    UPDATE rango_facturas SET actual = v_numero_factura WHERE id = v_id_rango;

    -- Calcular el monto total de la venta con IVA desde los detalles
    SELECT SUM(
        (detalle->>'cantidad')::INTEGER * (detalle->>'precio_unitario')::NUMERIC(10,2) + 
        ((detalle->>'cantidad')::INTEGER * (detalle->>'precio_unitario')::NUMERIC(10,2) * p.tipo_iva::NUMERIC / 100)
    ) INTO v_monto_total
    FROM jsonb_array_elements(p_detalles) AS detalle
    JOIN producto p ON (detalle->>'id_producto')::INTEGER = p.id_producto;

    IF v_monto_total IS NULL THEN
        RAISE EXCEPTION 'El monto total no puede ser calculado porque los detalles son inválidos';
    END IF;

    -- Si se ha proporcionado un monto total de servicios, sumarlo al total de la venta
    IF p_monto_total_servicios IS NOT NULL THEN
        v_monto_total := v_monto_total + p_monto_total_servicios;
    END IF;

    -- Si se ha proporcionado una nota de crédito, aplicar su monto
    IF p_nota_credito_id IS NOT NULL THEN
        SELECT monto INTO v_monto_nc_aplicado
        FROM notas_credito_debito
        WHERE id = p_nota_credito_id AND estado = 'pendiente';

        IF v_monto_nc_aplicado IS NULL THEN
            RAISE EXCEPTION 'La nota de crédito proporcionada no es válida o ya ha sido utilizada';
        END IF;

        -- Aplicar el monto de la nota de crédito al total de la venta
        v_monto_total := v_monto_total - v_monto_nc_aplicado;

        -- Actualizar el estado de la nota de crédito a "aplicada"
        UPDATE notas_credito_debito
        SET estado = 'aplicada', fecha_aplicacion = NOW(), venta_id = v_venta_id
        WHERE id = p_nota_credito_id;
    END IF;

    -- Insertar la cabecera de la venta incluyendo los campos solicitud_id y monto_total_servicios
    INSERT INTO ventas (
        cliente_id, fecha, forma_pago, estado, cuotas, numero_factura, timbrado, nota_credito_id, monto_nc_aplicado, metodo_pago, solicitud_id, monto_total_servicios
    )
    VALUES (
        p_cliente_id, p_fecha, p_forma_pago, 'pendiente', p_cuotas, v_numero_factura, v_timbrado, p_nota_credito_id, v_monto_nc_aplicado, p_metodo_pago, p_solicitud_id, p_monto_total_servicios
    )
    RETURNING id INTO v_venta_id;

    -- Insertar los detalles de la venta
    INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio_unitario)
    SELECT
        v_venta_id,
        (detalle->>'id_producto')::INTEGER,
        (detalle->>'cantidad')::INTEGER,
        (detalle->>'precio_unitario')::NUMERIC(10,2)
    FROM jsonb_array_elements(p_detalles) AS detalle;

    -- Si la venta es a cuotas, generar las cuentas por cobrar
    IF p_cuotas > 1 THEN
        v_monto_por_cuota := v_monto_total / p_cuotas;

        FOR i IN 1..p_cuotas LOOP
            v_fecha_vencimiento := p_fecha + ((i - 1) * INTERVAL '1 month');
            INSERT INTO cuentas_por_cobrar (
                venta_id, numero_cuota, fecha_vencimiento, monto, estado
            )
            VALUES (
                v_venta_id, i, v_fecha_vencimiento, v_monto_por_cuota, 'pendiente'
            );
        END LOOP;
    END IF;

    -- Si se ha proporcionado una solicitud_id, actualizar su estado a "facturado"
    IF p_solicitud_id IS NOT NULL THEN
        UPDATE servicios_cabecera
        SET estado = 'facturado'
        WHERE id_cabecera = p_solicitud_id;
    END IF;

    -- Retornar el ID de la venta generada
    RETURN v_venta_id;
END;
$BODY$;

ALTER FUNCTION public.generar_venta(integer, character varying, integer, jsonb, timestamp without time zone, character varying, integer, integer, numeric)
    OWNER TO postgres;
