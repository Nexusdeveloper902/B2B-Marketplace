<?php

return [
    'meta_title' => 'Solicitar una demo',

    'hero' => [
        'title' => 'Solicite una demo.',
        'body' => 'Cuéntenos sobre su organización y qué necesita contar. Respondemos dentro de dos días hábiles para coordinar un recorrido.',
    ],

    'form' => [
        'name' => 'Nombre completo',
        'email' => 'Correo de trabajo',
        'organization' => 'Organización',
        'tier' => 'Paquete de interés',
        'tier_placeholder' => 'Elija un paquete',
        'tier_options' => [
            'starter' => 'Starter — escuelas pequeñas',
            'campus' => 'Campus — escuelas grandes',
            'enterprise' => 'Enterprise — seguimiento personalizado',
            'unsure' => 'Aún no lo sé',
        ],
        'message' => '¿Qué le gustaría rastrear?',
        'message_hint' => 'Una o dos frases bastan: asistencia, comidas, reciclaje o algo personalizado.',
        'submit' => 'Enviar solicitud',
        'privacy' => 'Sus datos se usan solo para responder esta solicitud.',
        'aria_errors' => 'El formulario tiene errores',
    ],

    'next' => [
        'title' => 'Qué pasa después',
        'steps' => [
            [
                'title' => 'Respondemos en dos días hábiles',
                'body' => 'Un correo breve confirmando que recibimos su solicitud, con horarios propuestos.',
            ],
            [
                'title' => 'Un recorrido de 30 minutos',
                'body' => 'Demostramos la plataforma con su caso de uso — sus puertas, sus conteos, sus etiquetas.',
            ],
            [
                'title' => 'Un plan piloto y una cotización',
                'body' => 'Una propuesta concreta de despliegue: lectores, tarjetas, reportes y precio.',
            ],
        ],
    ],

    'thankyou' => [
        'title' => 'Solicitud recibida.',
        'body' => 'Registramos su solicitud y responderemos a :email dentro de dos días hábiles.',
        'home' => 'Volver a la página de inicio',
        'another' => 'Enviar otra solicitud',
    ],
];
