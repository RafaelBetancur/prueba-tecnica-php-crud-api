https://www.notion.so/API-CRUD-Manager-Prueba-T-cnica-24e0998cfe93806d9647c014fcf61559?source=copy_link

API CRUD Manager – Prueba Técnica
Descripción General
Este proyecto es una aplicación web CRUD desarrollada en PHP 8, con arquitectura por capas y principios SOLID, que consume una API externa, almacena los resultados en base de datos y permite mejorar los registros categorizados como bad hasta convertirlos en medium o good.

Incluye:

Carga inicial de datos desde la API
Barridos de mejora hasta eliminar todos los registros bad
Interfaz web para gestión CRUD
API interna para operaciones desde cURL



Estructura del Proyecto

/proyecto-api-crud/
├── config/
│   └── database.php       # Configuración de conexión a la BD (usa .env)
├── src/
│   ├── Controllers/       # Controladores que reciben y responden las peticiones HTTP
│   ├── Services/          # Lógica de negocio (API externa, mejoras, etc.)
│   ├── Repositories/      # Acceso a la base de datos
│   ├── Models/            # Representación de las entidades (ApiResult)
│   ├── DTOs/              # Objetos de transferencia de datos (ApiResponseDTO)
│   └── Exceptions/        # Manejo de errores personalizados
├── public/
│   ├── index.php          # Punto de entrada de la aplicación y ruteo básico
│   ├── index.html         # Interfaz web principal
│   ├── app.js             # Lógica frontend (fetch API interna, eventos)
│   └── styles.css         # Estilos de la interfaz
├── scripts/
│   └── initialize_db.php  # Script para crear base de datos y tablas
├── vendor/                # Dependencias instaladas con Composer
├── .env                   # Variables de entorno (no subir a git)
├── composer.json          # Configuración de dependencias


Tecnologías Usadas
Backend: PHP 8 + PDO + Composer
Frontend: HTML, CSS, JavaScript puro
Dependencias:
guzzlehttp/guzzle: cliente HTTP para consumir API externa.
vlucas/phpdotenv: manejo de variables de entorno.



Requisitos Previos

PHP 8.0+
MySQL
Composer
Servidor web XAMPP para entorno local (incluye Apache, PHP y MySQL)


Instalación y Configuración
1. Clonar el repositorio
	git clone https://github.com/RafaelBetancur/prueba-tecnica-php-crud-api.git
	cd prueba-tecnica-php-crud-api
2. Instalar dependencias
	composer install
3. Configurar variables de entorno
	# Tipo de conexión a la base de datos.
	# En este caso se usa MySQL, pero podría ser "pgsql" para PostgreSQL o "sqlite" para SQLite
	DB_CONNECTION=mysql

	# Dirección del servidor de base de datos
	DB_HOST=127.0.0.1

	# Puerto en el que MySQL escucha conexiones.
	# El valor por defecto en MySQL es 3306.
	DB_PORT=3306

	# Nombre de la base de datos donde se guardarán los datos de la aplicación
	# Debe existir o se creará con el script "initialize_db.php".
	DB_DATABASE=api_crud_test

	# Usuario de MySQL con permisos para acceder a la base de datos.
	DB_USERNAME=root

	# Contraseña del usuario de MySQL
	DB_PASSWORD=

	# URL base de la API externa que el sistema consumirá para obtener los datos iniciales.
	API_BASE_URL=https://4advance.co/testapi/get.php

	# Identificador único del usuario para la API externa.
	USER_ID=P01LAH



Inicializar la base de datos
	php scripts/initialize_db.php



Levantar el servidor
	php -S localhost:8080 -t public



Carga inicial de datos
	Desde la interfaz: clic en "Inicializar Datos".
	Por API: curl -X POST http://localhost:8080/api/initialize



Mejorar registros "bad"
	Desde la interfaz: clic en "Limpiar Registros Bad".
	Por API: curl -X POST http://localhost:8080/api/improve




Arquitectura y Principios
El proyecto aplica arquitectura en capas y principios SOLID:

Single Responsibility: cada clase tiene una única responsabilidad (p. ej., ApiService solo consume la API externa).
Open/Closed: el código está abierto a extensión y cerrado a modificación (inyección de dependencias).
Liskov Substitution: las clases pueden ser sustituidas por sus derivadas sin romper el código.
Interface Segregation: uso de DTOs y contratos claros para transferir datos.
Dependency Inversion: las dependencias (PDO, ApiService) se inyectan, facilitando cambios y pruebas.



Tabla: api_results
Campo			Tipo				Descripción
id			INT AI PK				Identificador único
value			INT				Valor devuelto por la API (0-100)
category			ENUM				Categoría (bad, medium, good)
attempt_number		INT				Número de intentos para mejorar
is_improved		TINYINT(1)			Si fue mejorado (1) o no (0)
created_at		TIMESTAMP			Fecha de creación
updated_at		TIMESTAMP			Fecha de última actualización


execution_logs
Campo			Tipo			Descripción
id			INT AI PK			Identificador
total_initial_calls		INT			Llamadas en la carga inicial
total_sweeps		INT			Número de barridos realizados
total_calls		INT			Llamadas totales (inicial + mejoras)
bad_count		INT			Registros finales en categoría bad
medium_count		INT			Registros finales en categoría medium
good_count		INT			Registros finales en categoría good
created_at		TIMESTAMP		Fecha de registro



