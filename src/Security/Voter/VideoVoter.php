<?php

namespace App\Security\Voter;

use App\Entity\Video;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use Symfony\Component\Security\Core\User\UserInterface;

class VideoVoter extends Voter
{
    public const EDIT = 'VIDEO_EDIT';
    public const VIEW = 'VIDEO_VIEW';
    public const DELETE = 'VIDEO_DELETE';

    private Security $security;

    public function __construct(Security $security) {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject = null): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && ($subject instanceof Video || !$subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEditVideo();
            case self::VIEW:
                return $this->canViewVideo();
            case self::DELETE:
                return $this->canDeleteVideo();
        }

        return false;
    }

    private function canEditVideo(): bool
    {
        return $this->security->isGranted('ROLE_AUTEUR');
    }

    private function canViewVideo(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }

    private function canDeleteVideo(): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
