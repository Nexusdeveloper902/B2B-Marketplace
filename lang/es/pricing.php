<?php

return [
    'meta_title' => 'Paquetes y precios',
    'meta_description' => 'Paquetes Starter, Campus y Enterprise sobre la misma plataforma de eventos de presencia. Instalación fija más costo por tarjeta para escuelas pequeñas; precios por volumen para campus; cotización personalizada para empresas.',

    'hero' => [
        'title' => 'Tres paquetes, una plataforma',
        'body' => 'Cada paquete corre la misma arquitectura de eventos de presencia: toque de tarjeta, evento identificado, registro con marca de tiempo, reporte. Los paquetes difieren en alcance — lectores, tarjetas y aplicaciones — no en naturaleza.',
    ],

    'tiers' => [
        [
            'name' => 'Starter',
            'audience' => 'para escuelas pequeñas',
            'price' => 'Instalación fija + por tarjeta',
            'footnote' => 'Una cuota única de instalación que incluye la puesta en marcha y la capacitación del personal, más un cargo pequeño por tarjeta.',
            'features' => [
                '1 lector en su entrada principal',
                'Hasta 200 tarjetas',
                'Eventos de asistencia: entrada y salida',
                'Reportes de asistencia diarios y mensuales',
                'Alertas de retraso por defecto',
                'Instalación y capacitación del personal incluidas',
                'Soporte por correo, respuesta el día hábil siguiente',
            ],
            'cta' => 'Solicitar una cotización',
        ],
        [
            'name' => 'Campus',
            'audience' => 'para escuelas y campus grandes',
            'price' => 'Instalación + por lector + por tarjeta',
            'footnote' => 'El precio de lectores y tarjetas aplica por unidad, con descuentos por volumen a medida que crecen las cantidades.',
            'features' => [
                'Todo lo de Starter',
                'De 2 a 10 lectores: accesos, pasillos, puntos de servicio',
                'Hasta 2.000 tarjetas',
                'Seguimiento de comidas PAE en los puntos de servicio',
                'Módulo de incentivos de reciclaje en los puntos de entrega',
                'Reportes por edificio y por grado',
                'Soporte prioritario, respuesta el mismo día',
            ],
            'cta' => 'Solicitar una cotización',
        ],
        [
            'name' => 'Enterprise',
            'audience' => 'para empresas y organizaciones',
            'price' => 'Cotización personalizada',
            'footnote' => 'Se cotiza después de una conversación de alcance: número de sedes, lectores, tarjetas y tipos de evento personalizados.',
            'features' => [
                'Plataforma de eventos de presencia completa',
                'Lectores y tarjetas ilimitados',
                'Tipos de evento personalizados para cualquier caso',
                'Acceso por API a su flujo de eventos',
                'Paneles, exportaciones e integraciones',
                'SLA con soporte prioritario',
                'Plan de despliegue y programa piloto',
            ],
            'cta' => 'Hable con nosotros',
        ],
    ],

    'strip' => [
        'title' => 'La misma arquitectura en cada paquete',
        'body' => 'Un reporte de asistencia del paquete Starter y un panel personalizado del paquete Enterprise leen el mismo tipo de registro. Subir de paquete suma lectores, tarjetas y tipos de evento — nunca significa migrar a un sistema distinto.',
        'pipeline' => ['toque', 'evento', 'registro', 'reporte'],
    ],
];
