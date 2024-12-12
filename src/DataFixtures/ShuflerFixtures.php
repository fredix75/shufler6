<?php

namespace App\DataFixtures;

use App\Entity\ChannelFlux;
use App\Entity\Flux;
use App\Entity\FluxMood;
use App\Entity\FluxType;
use App\Entity\MusicCollection\Album;
use App\Entity\MusicCollection\Track;
use App\Entity\Video;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Serializer\SerializerInterface;


class ShuflerFixtures extends Fixture
{
    private string $filepath;

    public function __construct(ParameterBagInterface $parameterBag, private readonly SerializerInterface $serializer) {
        $this->filepath = $parameterBag->get('resources')['fixtures_filepath'];
    }

    public function load(ObjectManager $manager): void
    {

        $file = fopen($this->filepath, 'r');

        $fixtures = json_decode(fread($file, 10000), true);

        if (!empty($fixtures['flux_type'])) {
            foreach ($fixtures['flux_type'] as $datas) {
                $fluxType = new FluxType();
                $fluxType->setName($datas['name']);
                $manager->persist($fluxType);
            }
            $manager->flush();
        }

        if (!empty($fixtures['flux_mood'])) {
            foreach ($fixtures['flux_mood'] as $datas) {
                $fluxMood = new FluxMood();
                $fluxMood->setName($datas['name']);
                $fluxMood->setType($manager->find(FluxType::class, $datas['type_id']));
                $manager->persist($fluxMood);
            }
            $manager->flush();
        }

        if (!empty($fixtures['channel_flux'])) {
            foreach ($fixtures['channel_flux'] as $datas) {
                $channelFlux = new ChannelFlux();
                $channelFlux->setName($datas['name']);
                $manager->persist($channelFlux);
            }
            $manager->flush();
        }

        if (!empty($fixtures['flux'])) {
            foreach ($fixtures['flux'] as $datas) {
                $flux = $this->serializer->denormalize($datas, Flux::class);
                $flux->setType($manager->find(FluxType::class, $datas['type_id']));
                if (!empty($datas['mood_id'])) {
                    $flux->setMood($manager->find(FluxMood::class, $datas['mood_id']));
                }
                if (!empty($datas['channel_id'])) {
                    $flux->setChannel($manager->find(ChannelFlux::class, $datas['channel_id']));
                }
                $manager->persist($flux);
            }
            $manager->flush();
        }

        if (!empty($fixtures['video'])) {
            foreach ($fixtures['video'] as $datas) {
                $video = $this->serializer->denormalize($datas, Video::class);
                $manager->persist($video);
            }
            $manager->flush();
        }

        if (!empty($fixtures['album'])) {
            foreach ($fixtures['album'] as $datas) {
                $album = $this->serializer->denormalize($datas, Album::class);
                $manager->persist($album);
            }
            $manager->flush();
        }

        if (!empty($fixtures['track'])) {
            foreach ($fixtures['track'] as $datas) {
                $track = $this->serializer->denormalize($datas, Track::class);
                $manager->persist($track);
            }
            $manager->flush();
        }
    }
}
