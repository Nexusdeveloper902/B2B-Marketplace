<?php

return [
    'meta_title' => 'Producto — cómo un toque se convierte en un registro',
    'meta_description' => 'Una tarjeta, un toque, un evento con marca de tiempo: el recorrido de Presence Platform desde la tarjeta NFC y el lector hasta la plataforma de eventos y los paneles.',

    'hero' => [
        'headline' => 'Una tarjeta. Un toque. Un evento con marca de tiempo.',
        'body' => 'El núcleo de Presence Platform es una estructura de datos pequeña: quién tocó, dónde, a qué hora exacta y qué cuenta ese toque. Todo lo que ofrece la plataforma — listas de asistencia, totales de comidas, programas de incentivos, paneles — se construye sobre ese único registro.',
    ],

    'pipeline' => [
        'title' => 'De la tarjeta al panel',
        'intro' => 'El evento hace el mismo recorrido cada vez, por cuatro estaciones.',
        'blocks' => [
            [
                'label' => 'TARJETA',
                'title' => 'Tarjeta NFC',
                'body' => 'Una tarjeta o llavero NFC con un identificador único. Sin batería, sin aplicación en el teléfono de nadie, nada que cargar ni emparejar.',
            ],
            [
                'label' => 'LECTOR',
                'title' => 'Lector de pared',
                'body' => 'Un lector en la puerta o punto de servicio lee la tarjeta y la empareja con su propio identificador, así cada evento sabe dónde ocurrió.',
            ],
            [
                'label' => 'PLATAFORMA',
                'title' => 'Plataforma de eventos',
                'body' => 'El backend sella el evento con una marca de tiempo del servidor y lo guarda como un registro inmutable — la única fuente de verdad.',
            ],
            [
                'label' => 'PANELES',
                'title' => 'Reportes y paneles',
                'body' => 'Los reportes de asistencia, comidas e incentivos son vistas calculadas sobre el mismo flujo de eventos — nunca una segunda copia de los datos.',
            ],
        ],
    ],

    'anatomy' => [
        'title' => 'Anatomía de un evento',
        'intro' => 'Cuatro campos. Esa es toda la idea.',
        'fields' => [
            [
                'key' => 'card',
                'title' => 'Quién',
                'body' => 'El identificador de la tarjeta apunta a un estudiante, miembro del personal o visitante. Las personas se identifican por lo que llevan, no por lo que teclean.',
            ],
            [
                'key' => 'reader',
                'title' => 'Dónde',
                'body' => 'El identificador del lector apunta a un punto conocido: una puerta, un pasillo, un punto de comida, una estación de entrega.',
            ],
            [
                'key' => 'at',
                'title' => 'Cuándo',
                'body' => 'Una marca de tiempo del servidor, asignada al recibir el evento y nunca editable después. El registro, no el dispositivo, es dueño del tiempo.',
            ],
            [
                'key' => 'type',
                'title' => 'Qué',
                'body' => 'La etiqueta del evento: attendance.in, meal.lunch, recycle.drop — o una etiqueta personalizada que su organización defina.',
            ],
        ],
        'sample_title' => 'Un evento, tal como se guarda',
    ],

    'apps' => [
        'title' => 'Construido sobre el mismo flujo',
        'intro' => 'Las tres aplicaciones estándar son reportes sobre los mismos registros de eventos. Nada se duplica, así nada puede quedar en desacuerdo.',
        'items' => [
            [
                'label' => 'attendance.in',
                'title' => 'Asistencia',
                'body' => 'Entrada y salida por persona y por día. Los reportes se construyen solos desde el flujo: presencia diaria, retrasos, ausencias por seguir. Un mes de registros es una consulta, no una carpeta de papeles.',
            ],
            [
                'label' => 'meal.lunch',
                'title' => 'Seguimiento de comidas (PAE)',
                'body' => 'Cada comida servida es un toque de una persona elegible. El total del día es exacto por construcción, concilia con los listados de elegibilidad, y la evidencia de cualquier comida cuestionada es una sola consulta.',
            ],
            [
                'label' => 'recycle.drop',
                'title' => 'Incentivos de reciclaje',
                'body' => 'Las entregas tocan una tarjeta en la estación y se acreditan al instante. Puntos, premios y posiciones son totales acumulados sobre eventos etiquetados — el programa se ejecuta solo mientras la evidencia se acumula.',
            ],
        ],
    ],

    'note' => [
        'title' => 'Agregar una cuarta aplicación es una etiqueta, no una reconstrucción',
        'body' => 'Como las aplicaciones son vistas sobre eventos etiquetados, un caso de uso nuevo significa definir una nueva etiqueta de evento y su reporte — no montar un sistema aparte. Ese es el mismo movimiento que hace el paquete Enterprise para el seguimiento personalizado.',
        'link' => 'Cómo funciona el seguimiento personalizado',
    ],
];
