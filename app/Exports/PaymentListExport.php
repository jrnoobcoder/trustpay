<?php

namespace App\Exports;

use App\Models\PaymentLinks;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentListExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths
{
	protected $startDate;
    protected $endDate;
	
	
	public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }
	
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return PaymentLinks::getExportData($this->startDate, $this->endDate);
    }
	
/* 	public function query()
    {

        return PaymentLinks::query();
    } */
	
	public function headings(): array
    {
        return [
            'ID',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Agent Name',
            'Amount',
            'Currency',
            'Description',
            'Payment Link',
            'Payment ID',
            'Payment Status',
			'Created At',
			'Updated At'
        ];
    }
	
	
	public function map($payments): array
    {
        return [
            $payments->id,
            $payments->customer_name,
            $payments->customer_email,
            $payments->customer_phone,
            User::getNameByAgentId($payments->agent_id),
            $payments->amount,
            $payments->currency,
            $payments->description,
            $payments->payment_link,
            $payments->payment_id,
            $payments->payment_status,
            $payments->created_at->format('Y-m-d H:i:s'),
            $payments->updated_at->format('Y-m-d H:i:s'),
        ];
    }
	
	
	public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,            
            'C' => 35,            
            'D' => 15,            
            'E' => 15,            
            'F' => 10,            
            'G' => 15,            
            'H' => 25,            
            'I' => 35,            
            'J' => 15,            
            'K' => 15,            
            'L' => 15,            
            'M' => 15,            
                               
        ];
    }
	
	public function filename(): string
    {
        $startDateFormatted = $this->startDate->format('Y-m-d');
        $endDateFormatted = $this->endDate->format('Y-m-d');
        return "payments_{$startDateFormatted}_to_{$endDateFormatted}.xlsx";
    }
}
