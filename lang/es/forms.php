<?php

return [
    'validation' => [
        'name.required' => 'Escriba su nombre completo.',
        'name.string' => 'Escriba su nombre completo.',
        'name.min' => 'Su nombre necesita al menos 2 caracteres.',
        'name.max' => 'Su nombre es demasiado largo.',

        'email.required' => 'Escriba su correo de trabajo.',
        'email.email' => 'Eso no parece un correo válido.',
        'email.max' => 'Su correo es demasiado largo.',

        'organization.required' => 'Escriba el nombre de su organización.',
        'organization.string' => 'Escriba el nombre de su organización.',
        'organization.min' => 'El nombre de su organización necesita al menos 2 caracteres.',
        'organization.max' => 'El nombre de su organización es demasiado largo.',

        'tier.required' => 'Elija un paquete de interés.',
        'tier.string' => 'Elija un paquete de interés.',
        'tier.in' => 'Elija un paquete de la lista.',

        'message.required' => 'Cuéntenos qué le gustaría rastrear.',
        'message.string' => 'Cuéntenos qué le gustaría rastrear.',
        'message.min' => 'Una o dos frases bastan (mínimo 10 caracteres).',
        'message.max' => 'Mantenga el mensaje por debajo de 2.000 caracteres.',
    ],
];
