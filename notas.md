1º PROYECTO
INPORTACIÓN DE DOCUMENTOS 

TAREAS

-botón que abra ventana modal
-ventana modal que recoja el formato a importar
-asegurar que el formato del archivo es de los soportados
-leer todos los campos de los datos
-comprobar que el archivo tenga los campos requeridos
-rechazar si no tiene esos campos como mínimo
-guardar los datos en la base de datos de laravel


1: importación de paquete laravel excel para la lectura del formato

composer require maatwebsite/excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

2: creación de las clases para importar usuario

php artisan make:import UsersImport --model=User

ToModel: esta es una clase genérica que crea el modelo con la lectura de la línea 0
WithHeadingRow: también se puede hacer con heading row, creando un array asociativo con el contenido de la cabecera
WithValidation: permite crear las reglas para poder validar determinados campos antes de importar

creado /app/imports/UsersImport.php
creado /app/Http/Controllers/ImportarExcelController.php

3: importador creado registro con todos los campos posibles

4: creación de reglas que requieran determinados campos (nombre, teléfono, correo)

Campos a incluir en el formulario:

Nombre*
Primer Apellido
Segundo Apellido
Email*
Teléfono Móvil*
Teléfono Fijo
DNI/CIF*
Cuenta Bancaria
Descripcion


2º PROYECTO
LECTOR PARA IMPORTACIÓN DE DOCUMENTOS