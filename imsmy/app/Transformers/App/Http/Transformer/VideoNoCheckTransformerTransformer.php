<?php

namespace App\Transformers\App\Http\Transformer;

use League\Fractal\TransformerAbstract;
use App\Entities\App\Http\Transformer\VideoNoCheckTransformer;

/**
 * Class VideoNoCheckTransformerTransformer
 * @package namespace App\Transformers\App\Http\Transformer;
 */
class VideoNoCheckTransformerTransformer extends TransformerAbstract
{

    /**
     * Transform the \VideoNoCheckTransformer entity
     * @param \VideoNoCheckTransformer $model
     *
     * @return array
     */
    public function transform(VideoNoCheckTransformer $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */

            'created_at' => $model->created_at,
            'updated_at' => $model->updated_at
        ];
    }
}
