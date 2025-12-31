<?php

namespace App\Twig\Components;

use App\Entity\Flux;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class FluxContent
{
    use DefaultActionTrait;

    #[LiveProp]
    private Flux $flux;

    #[LiveProp]
    private int $page;

    private array $items = [];

    public function getFlux(): Flux
    {
        return $this->flux;
    }

    public function setFlux(Flux $flux): void
    {
        $this->flux = $flux;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function mount(Flux $flux, int $page = 1): void
    {
        $this->flux = $flux;
        $this->page = $page;
        $this->items = $this->getDatas();
    }

    #[LiveAction]
    public function prevPage(): void
    {
        $this->page = $this->page > 1 ? $this->page - 1 : 1;
        $this->items = $this->getDatas();
    }

    #[LiveAction]
    public function nextPage(): void
    {
        $this->page++;
        $this->items = $this->getDatas();
    }

    public function getDatas(): array
    {
        $contenu = [];
        try {
            if (@simplexml_load_file($this->flux->getUrl(), null, LIBXML_NOCDATA)->{'channel'}->{'item'}) {
                $contenu = @simplexml_load_file($this->flux->getUrl())->{'channel'}->{'item'};
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        $debut = ($this->page - 1) * 6;
        $namespaces = $contenu ? $contenu->getNamespaces(true) : [];
        $infos = [];

        for ($i = $debut; $i < $debut + 6; $i++) {
            if (empty($contenu[$i])) {
                break;
            }
            $infos[$i] = $contenu[$i];
            $infos[$i]->title = stripcslashes($contenu[$i]->title);
            $infos[$i]->description = $contenu[$i]->description;
            if (!empty($namespaces['media']) && !empty($contenu[$i]->children($namespaces['media'])->attributes()->url)) {
                $infos[$i]->media = $contenu[$i]->children($namespaces['media'])->attributes()->url;
            }
        }

        return $infos;
    }
}
