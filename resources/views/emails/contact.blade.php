@component('mail::message')
# Nuevo mensaje de contacto

**Nombre:** {{ $name }}
**Email:** {{ $email }}
**Teléfono:** {{ $phone }}
**Servicio:** {{ $service }}

**Mensaje:**
{{ $userMessage }}

@endcomponent