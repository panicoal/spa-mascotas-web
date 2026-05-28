<?php

namespace App\Mail;

use App\Models\Cita;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PickupReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Cita $cita) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🐾 ¡Tu mascota está lista para recoger! — Pet Spa'
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.pickup-ready',
            with: [
                'mascotaNombre'    => $this->cita->pet->nombre ?? 'Tu mascota',
                'clienteNombre'    => $this->cita->client->nombre_completo ?? 'Estimado/a cliente',
                'groomerNombre'    => $this->cita->groomer->nombre_completo ?? 'Nuestro groomer',
                'servicio'         => $this->cita->service->nombre ?? 'Servicio de grooming',
                'fecha'            => $this->cita->fecha,
                'horaFin'          => $this->cita->hora_fin,
                'telefono'         => config('mail.spa_phone', '+1 000 000 0000'),
                'espaSpa'          => config('app.name', 'Pet Spa'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
