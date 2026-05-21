<?php

namespace App\Helpers;

class PaymentPlanHelper
{
    /**
     * Get payment plan configuration based on plan type
     */
    public static function getPlanConfiguration($planType)
    {
        switch ($planType) {
            case 'A':
                return [
                    'payment_initial_percentage' => 30,
                    'payment_construction_percentage' => 40,
                    'payment_delivery_percentage' => 30,
                    'legal_costs' => 500,
                    'payment_installments' => 0,
                    'description' => 'Plan A: 30% al firmar + $500 legales, 40% durante construcción, 30% a la entrega'
                ];
                
            case 'B':
                return [
                    'payment_initial_percentage' => 40,
                    'payment_construction_percentage' => 30,
                    'payment_delivery_percentage' => 30,
                    'legal_costs' => 500,
                    'payment_installments' => 5,
                    'description' => 'Plan B: 40% al firmar + $500 legales, 30% en 5 cuotas, 30% a la entrega'
                ];
                
            case 'C':
                return [
                    'payment_initial_percentage' => 50,
                    'payment_construction_percentage' => 20,
                    'payment_delivery_percentage' => 30,
                    'legal_costs' => 500,
                    'payment_installments' => 8,
                    'description' => 'Plan C: 50% al firmar + $500 legales, 20% en 8 cuotas, 30% a la entrega'
                ];
                
            default:
                return [
                    'payment_initial_percentage' => 30,
                    'payment_construction_percentage' => 40,
                    'payment_delivery_percentage' => 30,
                    'legal_costs' => 500,
                    'payment_installments' => 0,
                    'description' => 'Plan Personalizado: Configuración flexible'
                ];
        }
    }
    
    /**
     * Calculate payment breakdown for a reservation
     */
    public static function calculatePaymentBreakdown($reservation)
    {
        $totalPrice = floatval($reservation->unit_price);
        
        if ($reservation->payment_initial_percentage > 0) {
            $initialPercentage = floatval($reservation->payment_initial_percentage);
            $constructionPercentage = floatval($reservation->payment_construction_percentage);
            $deliveryPercentage = floatval($reservation->payment_delivery_percentage);
            $legalCosts = floatval($reservation->legal_costs);
            $installments = intval($reservation->payment_installments);
        } else {
            $config = self::getPlanConfiguration($reservation->payment_method);
            $initialPercentage = $config['payment_initial_percentage'];
            $constructionPercentage = $config['payment_construction_percentage'];
            $deliveryPercentage = $config['payment_delivery_percentage'];
            $legalCosts = $config['legal_costs'];
            $installments = $config['payment_installments'];
        }
        
        // Dynamic calculations based on current price
        $pagoInicialSinLegales = $totalPrice * $initialPercentage / 100;
        $pagoConstruccion = $totalPrice * $constructionPercentage / 100;
        $pagoEntrega = $totalPrice * $deliveryPercentage / 100;
        
        // Add legal costs to initial payment
        $pagoInicialConLegales = $pagoInicialSinLegales + $legalCosts;
        
        // Calculate installment amount if there are installments
        $cuota = $installments > 0 ? $pagoConstruccion / $installments : 0;
        
        return [
            'pago_inicial' => $pagoInicialConLegales,
            'pago_inicial_sin_legales' => $pagoInicialSinLegales,
            'pago_construccion' => $pagoConstruccion,
            'pago_entrega' => $pagoEntrega,
            'costos_legales' => $legalCosts,
            'cantidad_cuotas' => $installments,
            'cuota' => $cuota,
            'porcentaje_inicial' => $initialPercentage,
            'porcentaje_construccion' => $constructionPercentage,
            'porcentaje_entrega' => $deliveryPercentage,
            'total_sin_legales' => $totalPrice,
            'total_con_legales' => $totalPrice + $legalCosts
        ];
    }
    
    /**
     * Validate that percentages sum to 100%
     */
    public static function validatePercentages($initial, $construction, $delivery)
    {
        $total = $initial + $construction + $delivery;
        
        if (abs($total - 100) > 0.01) {
            throw new \Exception("Los porcentajes deben sumar 100%. Actual: {$total}%");
        }
        
        return true;
    }
}
