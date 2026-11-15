<?php

return [
    'array' => 'El campo :attribute debe ser una lista.',
    'between' => ['numeric' => 'El campo :attribute debe estar entre :min y :max.'],
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'distinct' => 'El campo :attribute tiene un valor repetido.',
    'enum' => 'El valor de :attribute no es válido.',
    'gt' => ['numeric' => 'El campo :attribute debe ser mayor que :value.'],
    'in' => 'El valor de :attribute no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'array' => 'El campo :attribute no debe tener más de :max elementos.',
        'numeric' => 'El campo :attribute no debe ser mayor que :max.',
        'string' => 'El campo :attribute no debe tener más de :max caracteres.',
    ],
    'min' => [
        'array' => 'El campo :attribute debe tener al menos :min elementos.',
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_unless' => 'El campo :attribute es obligatorio salvo que :other sea :values.',
    'string' => 'El campo :attribute debe ser texto.',
    'attributes' => [],
];
