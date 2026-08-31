<?php

declare(strict_types=1);
namespace Zoosper\Auth\Token;
use Zoosper\Auth\Model\AdminUser;
final readonly class PersonalAccessTokenPrincipal
{
    public function __construct(public PersonalAccessToken $token,public AdminUser $user){}
    public function allows(string $scope):bool{return $this->token->allows($scope);}
}










