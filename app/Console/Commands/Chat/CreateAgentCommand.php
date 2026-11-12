<?php

namespace App\Console\Commands\Chat;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAgentCommand extends Command
{
    protected $signature = 'chat:create-agent
        {--name= : Nombre completo del agente}
        {--email= : Correo electrónico}
        {--password= : Contraseña (mínimo 8 caracteres)}';

    protected $description = 'Crea un usuario agente con acceso al panel de chat (/admin)';

    public function handle(): int
    {
        $name  = (string) ($this->option('name')     ?? $this->ask('Nombre completo'));
        $email = (string) ($this->option('email')    ?? $this->ask('Correo electrónico'));
        $pass  = (string) ($this->option('password') ?? $this->secret('Contraseña (mín. 8 caracteres)'));

        $errors = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $pass],
            [
                'name'     => ['required', 'string', 'max:255'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8'],
            ],
        )->errors();

        if ($errors->isNotEmpty()) {
            foreach ($errors->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($pass),
            'is_agent' => true,
        ]);

        $this->info("Agente '{$name}' ({$email}) creado correctamente.");
        $this->line('  Acceso al panel: /admin');
        $this->line('  La primera vez se pedirá configurar la autenticación en dos pasos (TOTP).');
        $this->line('  Guarda los códigos de recuperación en un lugar seguro.');

        return self::SUCCESS;
    }
}
