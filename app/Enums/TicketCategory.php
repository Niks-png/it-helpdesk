<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TicketCategory: string implements HasLabel
{
    case Computer = 'computer';
    case Network = 'network';
    case Printer = 'printer';
    case Account = 'account';
    case Software = 'software';
    case Email = 'email';
    case Security = 'security';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Computer => 'Dators / klēpjdators',
            self::Network => 'Tīkls / internets',
            self::Printer => 'Printeris',
            self::Account => 'Konts / piekļuve',
            self::Software => 'Programmatūra',
            self::Email => 'E-pasts',
            self::Security => 'Drošība',
            self::Other => 'Cits',
        };
    }
}
