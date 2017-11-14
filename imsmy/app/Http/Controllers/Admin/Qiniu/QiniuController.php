<?php

namespace App\Http\Controllers\Admin\Qiniu;

use App\Http\Controllers\Admin\BaseSessionController;
use CloudStorage;

class QiniuController extends BaseSessionController
{

    /**
     * 上传七牛的token
     *
     * @return string
     */
    public function token()
    {
        return json_encode(['uptoken' => CloudStorage::getToken()]);
    }


}
