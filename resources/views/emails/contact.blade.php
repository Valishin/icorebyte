@component('mail::message')

<div style="text-align: center; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb; margin-bottom: 24px;">
  <h1 style="font-size: 22px; font-weight: 600; color: #111827; margin: 0;">Nuevo mensaje de contacto</h1>
  <p style="color: #6b7280; font-size: 14px; margin-top: 6px;">Has recibido un nuevo mensaje desde el formulario web</p>
</div>

<table style="width: 100%; border-collapse: collapse;">
  <tr>
    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6; width: 120px;">
      <span style="font-size: 13px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Nombre</span>
    </td>
    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
      <span style="font-size: 15px; color: #111827; font-weight: 500;">{{ $name }}</span>
    </td>
  </tr>
  <tr>
    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
      <span style="font-size: 13px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Email</span>
    </td>
    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
      <a href="mailto:{{ $email }}" style="font-size: 15px; color: #2563eb;">{{ $email }}</a>
    </td>
  </tr>
  <tr>
    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
      <span style="font-size: 13px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Teléfono</span>
    </td>
    <td style="padding: 10px 0; border-bottom: 1px solid #f3f4f6;">
      <span style="font-size: 15px; color: #111827;">{{ $phone }}</span>
    </td>
  </tr>
  <tr>
    <td style="padding: 10px 0;">
      <span style="font-size: 13px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Servicio</span>
    </td>
    <td style="padding: 10px 0;">
      <span style="display: inline-block; background: #eff6ff; color: #1d4ed8; font-size: 13px; font-weight: 500; padding: 3px 10px; border-radius: 20px;">{{ $service }}</span>
    </td>
  </tr>
</table>

<div style="margin-top: 24px; background: #f9fafb; border-left: 3px solid #2563eb; border-radius: 4px; padding: 16px 20px;">
  <p style="font-size: 13px; color: #6b7280; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 8px;">Mensaje</p>
  <p style="font-size: 15px; color: #111827; line-height: 1.6; margin: 0;">{{ $userMessage }}</p>
</div>

@endcomponent