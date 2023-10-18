<?php

namespace Ragnarok\Skyttel\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesPlace extends Model
{
    use HasFactory;

    protected $table = 'skyttel_transaction_salesplace';
    public $timestamps = false;
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
