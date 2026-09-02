<?php

return [
    'meta_title' => 'Enterprise — seguimiento personalizado de eventos',
    'meta_description' => 'El patrón tocar, identificar, marcar el tiempo y etiquetar aplicado a cualquier caso: registro de turnos, acceso a zonas, préstamo de activos, registro de visitantes. Tipos de evento personalizados con acceso por API.',

    'hero' => [
        'title' => 'Si un momento de presencia puede nombrarse, la plataforma puede contarlo.',
        'body' => 'El paquete Enterprise le entrega la plataforma completa de eventos de presencia con etiquetas personalizadas. El patrón nunca cambia: tocar, identificar, marcar el tiempo, etiquetar. Su organización decide qué significan las etiquetas.',
    ],

    'pattern' => [
        'title' => 'El patrón, una vez más',
        'intro' => 'Cada caso de uso personalizado es el mismo evento de cuatro campos, con una etiqueta que usted define.',
        'fields' => [
            [
                'key' => 'card',
                'title' => 'Quién',
                'body' => 'Una tarjeta, una persona — personal, contratista o visitante. Lo que llevan es su identidad en cada lector.',
            ],
            [
                'key' => 'reader',
                'title' => 'Dónde',
                'body' => 'Un lector, un punto conocido de su operación: muelle, laboratorio, bodega de herramientas, recepción.',
            ],
            [
                'key' => 'at',
                'title' => 'Cuándo',
                'body' => 'Una marca de tiempo del servidor, fijada al recibir el evento. Cuando el registro dice 06:59:58, esa es la hora que cuenta.',
            ],
            [
                'key' => 'type',
                'title' => 'Qué',
                'body' => 'La etiqueta que usted define: shift.begin, zone.enter, asset.out, visitor.in — o lo que su operación necesite nombrar.',
            ],
        ],
    ],

    'cases' => [
        'title' => 'Cómo se ve en la práctica',
        'intro' => 'Cuatro ejemplos de la misma estructura de evento, en cuatro trabajos distintos.',
        'items' => [
            [
                'time' => '06:59:58',
                'card' => '1188',
                'reader' => 'DOCK-1',
                'type' => 'shift.begin',
                'title' => 'Registro de turno',
                'body' => 'La entrada al muelle o al taller, con la misma calidad de evidencia que la asistencia escolar: marca de tiempo del servidor, persona, lugar. Las disputas de nómina terminan en el registro.',
            ],
            [
                'time' => '11:40:12',
                'card' => '0219',
                'reader' => 'LAB-B',
                'type' => 'zone.enter',
                'title' => 'Acceso a zonas',
                'body' => 'El personal toca para entrar a zonas restringidas — laboratorios, salas de servidores, bodegas. Los registros de acceso se construyen solos, y "quién estaba en el edificio" es una consulta, no una tarde de exportaciones del sistema de credenciales.',
            ],
            [
                'time' => '14:02:31',
                'card' => '0441',
                'reader' => 'TOOLSHED',
                'type' => 'asset.out',
                'title' => 'Préstamo de activos',
                'body' => 'Herramientas y equipo se prestan tocando la tarjeta de quien los toma, en la jaula o el estante. Quién tiene la herramienta, desde cuándo y desde dónde salió — un registro cada vez.',
            ],
            [
                'time' => '09:15:00',
                'card' => '9001',
                'reader' => 'RECEPTION',
                'type' => 'visitor.in',
                'title' => 'Registro de visitantes',
                'body' => 'Los visitantes reciben una tarjeta en recepción; sus entradas y salidas se registran como las de todos. El registro de visitantes es un reporte, no un portapapeles.',
            ],
        ],
    ],

    'includes' => [
        'title' => 'Qué incluye el paquete Enterprise',
        'items' => [
            'Tipos de evento personalizados — cualquier etiqueta que su operación necesite',
            'Acceso por API a su flujo completo de eventos',
            'Paneles, exportaciones e integraciones',
            'Lectores y tarjetas ilimitados en todas las sedes',
            'Plan de despliegue, programa piloto y capacitación del personal',
            'SLA con soporte prioritario',
        ],
        'link' => 'Converse sobre su caso de uso',
    ],
];
