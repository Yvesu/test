<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/3/17
 * Time: 19:21
 */

namespace App\Services;

use Illuminate\Pagination\BootstrapThreePresenter;
class Presenter extends BootstrapThreePresenter
{

    public function render()
    {
        if ($this->hasPages()) {
            return (sprintf(
                '<ul class="pagination pagination-lg pull-right">%s %s %s</ul>',
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            ));
        }

        return '';
    }
}