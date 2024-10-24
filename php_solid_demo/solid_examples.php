<?php
declare(strict_types=1);

/* 1. Single Responsibility Principle (SRP)

Definición: Una clase debe tener una y solo una razón para cambiar, es decir, debe tener una única responsabilidad.

La clase Task se encarga de gestionar la información de una tarea */

class Task {
    public function __construct(
        private string $title,
        private string $description
    ) {}

    public function getTitle(): string {
        return $this->title;
    }

    public function getDescription(): string {
        return $this->description;
    }
}

// La clase TaskPrinter se encarga de mostrar la información de la tarea
class TaskPrinter {
    public function printTask(Task $task): string {
        return "Tarea: {$task->getTitle()}, Descripción: {$task->getDescription()}";
    }
}

/* 2. Open/Closed Principle (OCP)

Definición: Las entidades de software (clases, módulos, funciones, etc.) deben estar abiertas para la extensión, 
            pero cerradas para la modificación.

Interfaz que define el comportamiento base */

interface Status {
    public function getStatusMessage(): string;
}

// Podemos agregar nuevos tipos de estado sin modificar el código existente
class TodoStatus implements Status {
    public function getStatusMessage(): string {
        return "La tarea está pendiente.";
    }
}

class InProgressStatus implements Status {
    public function getStatusMessage(): string {
        return "La tarea está en progreso.";
    }
}

class CompletedStatus implements Status {
    public function getStatusMessage(): string {
        return "La tarea está completada.";
    }
}

/* 3. Liskov Substitution Principle (LSP)

Definición: Las clases derivadas deben poder sustituir a sus clases base sin alterar la correcta ejecución del programa.

Clase base que define el comportamiento común */

abstract class Category {
    abstract public function getCategoryName(): string;
}

class WorkCategory extends Category {
    public function getCategoryName(): string {
        return "Trabajo";
    }
}

class PersonalCategory extends Category {
    public function getCategoryName(): string {
        return "Personal";
    }
}

/* 4. Interface Segregation Principle (ISP)

Definición: Un cliente no debe verse obligado a depender de interfaces que no utiliza. 
            Es mejor tener muchas interfaces específicas en lugar de una única interfaz general.

Interfaces específicas para diferentes comportamientos */

interface Editable {
    public function edit(string $title, string $description): void;
}

interface Deletable {
    public function delete(): void;
}

// La clase EditableTask implementa solo las interfaces necesarias
class EditableTask extends Task implements Editable, Deletable {
    public function edit(string $title, string $description): void {
        // Simula editar la tarea
        echo "Editando tarea: $title\n";
    }

    public function delete(): void {
        // Simula eliminar la tarea
        echo "Tarea eliminada: {$this->getTitle()}\n";
    }
}

/* 5. Dependency Inversion Principle (DIP)

Definición: Los módulos de alto nivel no deben depender de módulos de bajo nivel, 
            ambos deben depender de abstracciones (interfaces). Además, las abstracciones 
            no deben depender de detalles, los detalles deben depender de abstracciones.
            
Interfaz de almacenamiento */

interface Storage {
    public function save(Task $task): void;
}

// Implementaciones concretas
class FileStorage implements Storage {
    public function save(Task $task): void {
        // Simula guardar en un archivo
        echo "Guardando en archivo: {$task->getTitle()}\n";
    }
}

class DatabaseStorage implements Storage {
    public function save(Task $task): void {
        // Simula guardar en una base de datos
        echo "Guardando en base de datos: {$task->getTitle()}\n";
    }
}

// Clase de alta nivel que depende de una abstracción
class TaskManager {
    public function __construct(
        private Storage $storage
    ) {}

    public function createTask(Task $task): void {
        $this->storage->save($task);
    }
}

// Ejemplo de uso
function runExample(): void {
    echo "Ejemplo de SOLID:\n\n";

    // SRP
    $task = new Task("Completar informe", "El informe debe ser enviado antes del viernes.");
    $printer = new TaskPrinter();
    echo "1. SRP - " . $printer->printTask($task) . "\n";

    // OCP
    $status = new TodoStatus();
    echo "2. OCP - Estado de la tarea: " . $status->getStatusMessage() . "\n";

    // LSP
    $workCategory = new WorkCategory();
    $personalCategory = new PersonalCategory();
    echo "3. LSP - Categoría de la tarea: " . $workCategory->getCategoryName() . "\n";
    echo "3. LSP - Categoría de la tarea: " . $personalCategory->getCategoryName() . "\n";

    // ISP
    $editableTask = new EditableTask("Hacer la compra", "Comprar frutas y verduras.");
    $editableTask->edit("Hacer la compra", "Comprar frutas, verduras y pan.");
    $editableTask->delete();

    // DIP
    $fileStorage = new TaskManager(new FileStorage());
    $dbStorage = new TaskManager(new DatabaseStorage());
    echo "5. DIP - ";
    $fileStorage->createTask($task);
    echo "5. DIP - ";
    $dbStorage->createTask($task);
}

// Ejecutar los ejemplos
runExample();
