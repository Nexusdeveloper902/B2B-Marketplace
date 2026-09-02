<?php

return [
    'meta_title' => 'Presence Platform — cada toque de tarjeta se convierte en un registro',
    'meta_description' => 'Infraestructura de eventos de presencia para escuelas y organizaciones. Una tarjeta NFC, un toque, un evento con marca de tiempo: asistencia, seguimiento de comidas e incentivos de reciclaje.',

    'hero' => [
        'headline' => 'Cada toque de tarjeta se convierte en un registro confiable.',
        'pitch' => 'Presence Platform es infraestructura de eventos de presencia: una tarjeta NFC, un toque, un evento con marca de tiempo. La asistencia, el servicio de comidas y los incentivos de reciclaje leen del mismo flujo.',
        'cta_primary' => 'Véalo en acción',
        'cta_secondary' => 'Solicitar una demo',
    ],

    'ledger' => [
        'title' => 'Registro de eventos — Colegio Riverside',
        'live' => 'registrando',
        'aria' => 'Registro de eventos ilustrativo: toques de tarjeta con hora, tarjeta, lector y etiqueta de evento',
        'columns' => ['hora', 'tarjeta', 'lector', 'evento'],
        'card_label' => 'TARJETA',
        'tap_aria' => 'Ilustración de una tarjeta tocando un lector',
    ],

    'problem' => [
        'title' => 'El problema es el registro.',
        'body_1' => 'Las escuelas y organizaciones cuentan lo que importa: quién llegó, quién comió, quién participó, qué volvió para reciclarse. En casi todas partes, ese conteo se sigue haciendo a mano — listas de asistencia, contadores manuales, hojas de cálculo y memoria.',
        'body_2' => 'Los conteos manuales se desvían. Los totales se cuestionan en las auditorías. Armar un mes de reportes toma días que nadie tiene. Y cuando los números deciden financiamiento, cumplimiento o personal, "probablemente" no es suficiente.',
        'costs_title' => 'Lo que cuesta un registro débil',
        'costs' => [
            'Disputas de asistencia que no se pueden resolver con evidencia',
            'Conteos del programa de comidas que no concilian con los listados de elegibilidad',
            'Programas de incentivos que se estancan porque nadie puede totalizarlos',
        ],
    ],

    'steps' => [
        'title' => 'Del toque al reporte',
        'intro' => 'La plataforma registra el mismo evento de cuatro partes cada vez, sin importar qué aplicación lo lea.',
        'items' => [
            [
                'title' => 'Toque',
                'body' => 'Un estudiante o miembro del personal acerca su tarjeta NFC a un lector de pared: en la puerta, en el punto de servicio o en la estación de entrega. Sin aplicación, sin batería, nada que emparejar.',
            ],
            [
                'title' => 'Identificación',
                'body' => 'El lector lee el identificador único de la tarjeta y lo empareja con su propio identificador de lector, así cada evento sabe quién y dónde.',
            ],
            [
                'title' => 'Marca de tiempo',
                'body' => 'La plataforma sella el evento cuando llega y lo guarda como un registro inmutable — no como una celda que alguien puede sobrescribir.',
            ],
            [
                'title' => 'Reporte',
                'body' => 'Los paneles y reportes leen el mismo flujo de eventos. Asistencia, comidas e incentivos son totales sobre los mismos registros, no sistemas separados.',
            ],
        ],
    ],

    'apps' => [
        'title' => 'Tres aplicaciones, un mismo flujo de eventos',
        'intro' => 'Cada paquete corre la misma arquitectura. Las aplicaciones son reportes distintos sobre los mismos registros.',
        'items' => [
            [
                'label' => 'attendance.in',
                'title' => 'Asistencia',
                'body' => 'Entrada y salida por persona y por día, en cada lector que instale. Los totales diarios se construyen solos; los retrasos y las ausencias se marcan solos en lugar de buscarse.',
            ],
            [
                'label' => 'meal.lunch',
                'title' => 'Seguimiento de comidas (PAE)',
                'body' => 'Cada comida subsidiada queda asociada al estudiante elegible que la tocó. Los conteos concilian por construcción con sus listados del programa, y el día termina con un total, no con una estimación.',
            ],
            [
                'label' => 'recycle.drop',
                'title' => 'Incentivos de reciclaje',
                'body' => 'Las entregas en los puntos de reciclaje se acreditan a la persona o grupo que tocó. Los totales de incentivos se calculan solos, y la evidencia de cada punto está a una consulta de distancia.',
            ],
        ],
    ],

    'audience' => [
        'title' => 'Para quién es',
        'items' => [
            [
                'title' => 'Escuelas pequeñas',
                'body' => 'Un lector en la entrada principal y hasta 200 tarjetas. Asistencia defendible en cualquier reunión.',
                'link' => 'Ver el paquete Starter',
                'href' => 'pricing',
            ],
            [
                'title' => 'Escuelas y campus grandes',
                'body' => 'Lectores en accesos, puntos de servicio y estaciones de reciclaje, con seguimiento de comidas e incentivos incluidos.',
                'link' => 'Ver el paquete Campus',
                'href' => 'pricing',
            ],
            [
                'title' => 'Empresas y organizaciones',
                'body' => 'Tipos de evento personalizados para lo que necesite contar: turnos, zonas, activos, visitas.',
                'link' => 'Ver Enterprise',
                'href' => 'enterprise',
            ],
        ],
    ],

    'closing' => [
        'title' => 'Vea lo que un toque puede hacer.',
        'body' => 'Recorra el producto en dos minutos, o díganos qué necesita contar y le mostraremos los eventos.',
        'cta_primary' => 'Véalo en acción',
        'cta_secondary' => 'Solicitar una demo',
    ],
];
