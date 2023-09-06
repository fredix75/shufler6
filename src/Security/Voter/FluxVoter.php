<?php

namespace App\Security\Voter;

use App\Entity\Flux;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class FluxVoter extends Voter
{
    public const EDIT = 'FLUX_EDIT';
    public const DELETE = 'FLUX_DELETE';

    private Security $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::DELETE])
            && ($subject instanceof Flux || !$subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::EDIT:
                return $this->canEditFlux();

            case self::DELETE:
                return $this->canDeleteFlux();
        }

        return false;
    }

    private function canEditFlux(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDeleteFlux(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
