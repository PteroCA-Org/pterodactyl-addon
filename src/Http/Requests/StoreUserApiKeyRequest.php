<?php

namespace Pteroca\PterodactylAddon\Http\Requests;

use Pterodactyl\Http\Requests\Api\Application\ApplicationApiRequest;
use Pterodactyl\Services\Acl\Api\AdminAcl as Acl;

class StoreUserApiKeyRequest extends ApplicationApiRequest
{
    protected ?string $resource = Acl::RESOURCE_USERS;

    protected int $permission = Acl::WRITE;

    public function authorize(): bool
    {
        // Defense in depth: only root administrators may mint keys for other accounts.
        return (bool) optional($this->user())->root_admin && parent::authorize();
    }

    public function rules(): array
    {
        return [
            'description' => 'required|string|min:1|max:500',
            'allowed_ips' => 'nullable|array|max:50',
            'allowed_ips.*' => [
                'string',
                function (string $attribute, $value, \Closure $fail) {
                    $ip = is_string($value) ? explode('/', $value, 2) : [''];
                    $valid = filter_var($ip[0], FILTER_VALIDATE_IP) !== false;
                    if ($valid && isset($ip[1])) {
                        $max = str_contains($ip[0], ':') ? 128 : 32;
                        $valid = ctype_digit($ip[1]) && (int) $ip[1] <= $max;
                    }
                    if (!$valid) {
                        $fail('The ' . $attribute . ' must be a valid IP address or CIDR range.');
                    }
                },
            ],
        ];
    }
}
