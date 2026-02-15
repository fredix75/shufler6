<?php

namespace App\Helper;

class FluxHelper
{
    public function getContent(string $url, int $nbByPage, int $offset = 0): array {
        $contenu = '';
        try {
            if (@simplexml_load_file($url, null, LIBXML_NOCDATA)->{'channel'}->{'item'}) {
                $contenu = @simplexml_load_file($url)->{'channel'}->{'item'};
            }
        } catch (\Exception $e) {
            return [];
        }

        $namespaces = $contenu ? $contenu->getNamespaces(true) : [];
        for ($i = $offset; $i < $offset + $nbByPage; $i++) {
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
