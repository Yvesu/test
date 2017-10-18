<?php


namespace App\Api\Transformer\Project;

use App\Api\Transformer\Transformer;
use App\Api\Transformer\UsersTransformer;
use CloudStorage;

class ProjectTransformer extends Transformer
{
    protected $usersTransformer;

    public function __construct(UsersTransformer $usersTransformer)
    {
        $this->usersTransformer = $usersTransformer;
    }

    public function transform($data)
    {
        return [
            'id'         => $data->id,
            'user'       => $this->usersTransformer->transform($data->belongsToUser),
            'name'       => $data->name,
            'film'       => $data->hasOneFilm->name,
            'intro'      => $data->hasOneIntro->intro,
            'amount'     => $data->amount,
            'amount_done'=> $data->hasManyInvestor->sum('amount'),
            'amount_users'=> $data->hasManyInvestor->count(),
            'team'       => $data->hasManyTeam->count() ? $this->usersTransformer->transformCollection($data->hasManyTeam->all()) : [],
            'progress'   => $data->hasManyProgress->last(),
            'contacts'   => $data->contacts,
            'phone'      => $data->phone,
            'city'       => $data->city,
            'days'       => $data->days,
            'from_time'  => $data->from_time,
            'end_time'   => $data->end_time,
            'active'     => $data->active,
            'cover'      => CloudStorage::downloadUrl($data->cover),
            'video'      => $data->video ? CloudStorage::downloadUrl($data->video) : '',
        ];
    }
}