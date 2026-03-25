<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allergen Configuration
    |--------------------------------------------------------------------------
    |
    | Each key is the allergen slug used in dish data. The label, background
    | colour and SVG icon fragment are consumed by allergen-icon components.
    |
    */

    'allergens' => [
        'gluten' => ['label' => 'Gluten', 'bg' => '#D97706', 'icon' => '<path fill="white" d="M8 1.5C6.5 3 5 5.5 5 7.5c0 1 .4 1.9 1 2.6V14h4V10.1c.6-.7 1-1.6 1-2.6 0-2-1.5-4.5-3-6z"/>'],
        'nuts'   => ['label' => 'Nuts',   'bg' => '#92400E', 'icon' => '<ellipse cx="8" cy="9.5" rx="5" ry="5.5" fill="white"/><path d="M5.5 5C5.5 3.3 6.6 2 8 2s2.5 1.3 2.5 3" stroke="#92400E" stroke-width="1" fill="none" stroke-linecap="round"/>'],
        'milk'   => ['label' => 'Milk',   'bg' => '#0284C7', 'icon' => '<path fill="white" d="M6 2h4l.5 2.5H5.5L6 2zM5 5h6l-1 9H6L5 5z"/>'],
        'wheat'  => ['label' => 'Wheat',  'bg' => '#CA8A04', 'icon' => '<line x1="8" y1="14" x2="8" y2="4" stroke="white" stroke-width="1.5"/><ellipse cx="5.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5.5 6)"/><ellipse cx="10.5" cy="6" rx="2.5" ry="1.5" fill="white" transform="rotate(20 10.5 6)"/><ellipse cx="5" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(-20 5 9)"/><ellipse cx="11" cy="9" rx="2.5" ry="1.5" fill="white" transform="rotate(20 11 9)"/><ellipse cx="8" cy="3" rx="1.5" ry="2" fill="white"/>'],
        'fish'   => ['label' => 'Fish',   'bg' => '#0891B2', 'icon' => '<path fill="white" d="M2 8c2-3 5-4 8-4s6 1 8 4c-2 3-5 4-8 4S4 11 2 8z"/><circle cx="13" cy="8" r="1.2" fill="#0891B2"/>'],
        'egg'    => ['label' => 'Egg',    'bg' => '#7C3AED', 'icon' => '<ellipse cx="8" cy="9" rx="5" ry="6" fill="white"/><ellipse cx="8" cy="10" rx="2.5" ry="3" fill="#7C3AED"/>'],
    ],

];
