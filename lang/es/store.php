<?php

/*
| Interfaz de la tienda (capa marketplace TASK-014): solo ETIQUETAS de
| navegación, catálogo, lista de cotización y comparación. Los datos del
| producto nunca se definen aquí — se leen de landing/product/pricing/
| enterprise para que no diverjan.
*/

return [
    'breadcrumb' => [
        'aria' => 'Ruta de navegación',
        'home' => 'Inicio',
    ],

    'categories' => [
        'title' => 'Explore el catálogo',
        'packages' => 'Paquetes',
        'packages_meta' => ':count paquetes',
        'apps' => 'Aplicaciones',
        'apps_meta' => ':count aplicaciones',
        'cases' => 'Seguimiento personalizado',
        'cases_meta' => ':count casos de uso',
        'how' => 'Cómo funciona',
        'how_meta' => ':count estaciones',
    ],

    'featured' => 'Destacado',
    'ticker_aria' => 'Etiquetas de evento mostradas en este sitio',
    'see_all' => 'Ver todo',
    'details' => 'Detalles',
    'included_in' => 'Incluido en',
    'prev' => 'Anterior',
    'next' => 'Siguiente',

    'quote' => [
        'button' => 'Cotización',
        'count_aria' => 'elementos en su lista de cotización',
        'title' => 'Su lista de cotización',
        'note' => 'Lo que agregue aquí se adjunta a su solicitud de cotización. No es un carrito: en este sitio no se compra nada y nuestro equipo cotiza los precios.',
        'empty' => 'Su lista de cotización está vacía. Agregue paquetes, aplicaciones o casos de uso desde el catálogo.',
        'add' => 'Agregar a cotización',
        'added' => 'En la cotización',
        'remove' => 'Quitar',
        'close' => 'Cerrar',
        'clear' => 'Vaciar lista',
        'cta' => 'Solicitar una cotización',
        'browse' => 'Ver paquetes',
        'message_prefix' => 'Lista de cotización:',
        'summary_empty' => 'Aún no hay elementos. Agregue algunos desde el catálogo, o simplemente describa lo que necesita en el formulario.',
        'groups' => [
            'pkg' => 'Paquete',
            'app' => 'Aplicación',
            'case' => 'Caso de uso',
        ],
    ],

    'compare' => [
        'title' => 'Compare los paquetes',
        'intro' => 'Cada línea se toma de las descripciones de los paquetes de arriba.',
        'feature' => 'Característica',
        'included' => 'Incluido',
        'not_listed' => 'No indicado',
        'rows' => [
            'price' => 'Modelo de precio',
            'readers' => 'Lectores',
            'cards' => 'Tarjetas',
            'attendance' => 'Asistencia (attendance.in)',
            'meals' => 'Seguimiento de comidas (meal.lunch)',
            'recycling' => 'Incentivos de reciclaje (recycle.drop)',
            'custom' => 'Tipos de evento personalizados',
            'api' => 'Acceso por API',
            'reports' => 'Reportes',
            'onboarding' => 'Puesta en marcha',
            'support' => 'Soporte',
        ],
    ],

    'pdp' => [
        'gallery_aria' => 'Ilustración: un toque de tarjeta en GATE-A se convierte en un evento almacenado',
        'choose' => 'Elija un paquete',
        'apps' => 'Aplicaciones',
        'fields' => 'Cada evento guarda',
        'tabs_aria' => 'En esta página',
    ],
];
