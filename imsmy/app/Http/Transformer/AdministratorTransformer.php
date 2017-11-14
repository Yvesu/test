<?php
namespace App\Http\Transformer;

class AdministratorTransformer extends Transformer
{
    public function transform($data)
    {
        $count = $data -> hasManyLoginLog -> count();

        return [
            'name'  => $data -> name,
            'login_count'  => $count,
            'last_time'  => $count ? $data -> hasManyLoginLog -> last() -> time : $data -> hasManyLoginLog -> count(),
            'last_ip'  => $count ? long2ip($data -> hasManyLoginLog -> last() -> ip) : $data -> hasManyLoginLog -> count(),
        ];
    }
}