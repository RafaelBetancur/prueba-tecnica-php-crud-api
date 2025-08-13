<?php

namespace App\Exceptions;
use Throwable;  // Esta es la línea que faltaba
use Exception;

class ApiException extends Exception
{
    // Puedes añadir propiedades personalizadas si lo necesitas
    protected $context;

    public function __construct(string $message = "", int $code = 0, array $context = [], ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    // Método para obtener el contexto adicional del error
    public function getContext(): array
    {
        return $this->context;
    }
}