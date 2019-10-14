<?php

namespace App\Observers;

use App\Models\ConversionOrder;

class ConversionOrderObserver
{
    /**
     * Handle the conversion order "created" event.
     *
     * @param  \App\Models\ConversionOrder  $conversionOrder
     * @return void
     */
    public function created(ConversionOrder $conversionOrder)
    {
        $user_wallet = $conversionOrder->user->user_wallet;
        $user_wallet->increment('conversion_total', $conversionOrder['order_money']);
    }

    /**
     * Handle the conversion order "updated" event.
     *
     * @param  \App\Models\ConversionOrder  $conversionOrder
     * @return void
     */
    public function updated(ConversionOrder $conversionOrder)
    {
        //
    }

    /**
     * Handle the conversion order "deleted" event.
     *
     * @param  \App\Models\ConversionOrder  $conversionOrder
     * @return void
     */
    public function deleted(ConversionOrder $conversionOrder)
    {
        //
    }

    /**
     * Handle the conversion order "restored" event.
     *
     * @param  \App\Models\ConversionOrder  $conversionOrder
     * @return void
     */
    public function restored(ConversionOrder $conversionOrder)
    {
        //
    }

    /**
     * Handle the conversion order "force deleted" event.
     *
     * @param  \App\Models\ConversionOrder  $conversionOrder
     * @return void
     */
    public function forceDeleted(ConversionOrder $conversionOrder)
    {
        //
    }
}
