<?php

/*
 * Demo users for DemoSeeder (#14). Fixed list, so the data is always the same.
 * The email is "<slug>@demo.librolibre.test" and the google_id "demo-<slug>" (set by DemoSeeder):
 * nobody can log in as them with Google; use `php artisan demo:token <email>` instead.
 * Postal codes are real; coordinates are rounded to 2 decimals (≈ 1 km), as the app stores them.
 */

return [
    // ---------------------------------------------------------------- admins
    ['slug' => 'laura', 'username' => 'Laura', 'role' => 'admin', 'postal_code' => '01005', 'latitude' => 42.85, 'longitude' => -2.68, 'languages' => ['es', 'eu', 'en'], 'description' => 'Administradora de LibroLibre. Leo de todo, sobre todo novela negra.'],
    ['slug' => 'iker', 'username' => 'Iker', 'role' => 'admin', 'postal_code' => '48007', 'latitude' => 43.27, 'longitude' => -2.94, 'languages' => ['es', 'eu'], 'description' => 'Administrador. Ciencia ficción y cómic.'],

    // ---------------------------------------------------------------- Vitoria-Gasteiz (Álava)
    ['slug' => 'ane', 'username' => 'Ane', 'postal_code' => '01001', 'latitude' => 42.85, 'longitude' => -2.67, 'languages' => ['es', 'eu'], 'description' => 'Me encantan las novelas largas y los libros de viajes.'],
    ['slug' => 'mikel', 'username' => 'Mikel', 'postal_code' => '01010', 'latitude' => 42.86, 'longitude' => -2.69, 'languages' => ['es', 'eu', 'en'], 'description' => 'Fantasía y ciencia ficción. Cambio sin problema.'],
    ['slug' => 'nerea', 'username' => 'Nerea', 'postal_code' => '01012', 'latitude' => 42.84, 'longitude' => -2.68, 'languages' => ['es'], 'description' => 'Profesora. Libros infantiles y juveniles para mis alumnos.'],
    ['slug' => 'jon', 'username' => 'Jon', 'postal_code' => '01008', 'latitude' => 42.85, 'longitude' => -2.69, 'languages' => ['es', 'eu'], 'description' => null],
    ['slug' => 'maria', 'username' => 'María', 'postal_code' => '01470', 'latitude' => 43.05, 'longitude' => -3.00, 'languages' => ['es'], 'description' => 'Vivo en Amurrio. Cocina y biografías.'],

    // ---------------------------------------------------------------- Bilbao y alrededores (Bizkaia)
    ['slug' => 'amaia', 'username' => 'Amaia', 'postal_code' => '48001', 'latitude' => 43.26, 'longitude' => -2.93, 'languages' => ['es', 'eu', 'fr'], 'description' => 'Poesía y ensayo. Prefiero quedar en el Casco Viejo.'],
    ['slug' => 'unai', 'username' => 'Unai', 'postal_code' => '48011', 'latitude' => 43.26, 'longitude' => -2.94, 'languages' => ['es', 'eu'], 'description' => 'Cómics y novela gráfica.'],
    ['slug' => 'maite', 'username' => 'Maite', 'postal_code' => '48013', 'latitude' => 43.26, 'longitude' => -2.95, 'languages' => ['es'], 'description' => 'Novela romántica y de época.'],
    ['slug' => 'asier', 'username' => 'Asier', 'postal_code' => '48990', 'latitude' => 43.36, 'longitude' => -3.01, 'languages' => ['es', 'en'], 'description' => 'De Getxo. Clásicos en inglés.'],
    ['slug' => 'carlos', 'username' => 'Carlos', 'postal_code' => '48010', 'latitude' => 43.26, 'longitude' => -2.93, 'languages' => ['es', 'en'], 'description' => 'Historia y divulgación científica.'],
    ['slug' => 'pablo', 'username' => 'Pablo', 'postal_code' => '48003', 'latitude' => 43.25, 'longitude' => -2.92, 'languages' => ['es'], 'description' => null],

    // ---------------------------------------------------------------- Donostia y alrededores (Gipuzkoa)
    ['slug' => 'leire', 'username' => 'Leire', 'postal_code' => '20001', 'latitude' => 43.32, 'longitude' => -1.98, 'languages' => ['es', 'eu'], 'description' => 'Literatura en euskera y novela contemporánea.'],
    ['slug' => 'gorka', 'username' => 'Gorka', 'postal_code' => '20012', 'latitude' => 43.31, 'longitude' => -1.97, 'languages' => ['es', 'eu'], 'description' => 'Terror y suspense.'],
    ['slug' => 'irati', 'username' => 'Irati', 'postal_code' => '20008', 'latitude' => 43.31, 'longitude' => -2.00, 'languages' => ['eu', 'es', 'fr'], 'description' => 'Estudiante. Busco libros de texto y ensayo.'],
    ['slug' => 'xabier', 'username' => 'Xabier', 'postal_code' => '20600', 'latitude' => 43.18, 'longitude' => -2.47, 'languages' => ['eu', 'es'], 'description' => 'De Eibar. Autoayuda y biografías.'],
    ['slug' => 'itziar', 'username' => 'Itziar', 'postal_code' => '20004', 'latitude' => 43.32, 'longitude' => -1.98, 'languages' => ['es', 'eu', 'en'], 'description' => 'Todo lo que tenga dragones.'],

    // ---------------------------------------------------------------- suspended (an admin suspended them; see DemoSeeder)
    ['slug' => 'lucia', 'username' => 'Lucía', 'status' => 'suspended', 'strikes' => 3, 'postal_code' => '48007', 'latitude' => 43.27, 'longitude' => -2.94, 'languages' => ['es'], 'description' => null, 'suspension_reason' => 'Tres denuncias confirmadas por no presentarse a los intercambios.'],
    ['slug' => 'sergio', 'username' => 'Sergio', 'status' => 'suspended', 'strikes' => 2, 'postal_code' => '01005', 'latitude' => 42.85, 'longitude' => -2.68, 'languages' => ['es'], 'description' => 'Cambio libros de todo tipo.', 'suspension_reason' => 'Mensajes ofensivos a otros usuarios en el chat.'],
];
