<?php

namespace Pteroca\PterodactylAddon\Http\Requests;

use Pterodactyl\Http\Requests\Api\Application\ApplicationApiRequest;
use Pterodactyl\Services\Acl\Api\AdminAcl as Acl;

class GetUsersApiKeysRequest extends ApplicationApiRequest
{
    protected ?string $resource = Acl::RESOURCE_USERS;

    protected int $permission = Acl::READ;

    public function authorize(): bool
    {
        return (bool) optional($this->user())->root_admin && parent::authorize();
    }
}
