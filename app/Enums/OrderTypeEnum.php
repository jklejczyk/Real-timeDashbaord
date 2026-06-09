<?php

namespace App\Enums;

enum OrderTypeEnum: string
{
    case Installation = 'installation';
    case Repair = 'repair';
    case Maintenance = 'maintenance';
    case Inspection = 'inspection';
}
