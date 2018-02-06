<?php

namespace App;

use Generator;
use Illuminate\Contracts\Filesystem\Filesystem;

class SiteRepository
{

    /**
     * @var Filesystem
     */
    private $disk;

    /**
     * @var array|null
     */
    private $sites;

    /**
     * SiteRepository constructor.
     *
     * @param Filesystem $disk
     */
    public function __construct(Filesystem $disk)
    {
        $this->disk = $disk;
    }

    /**
     * @param $slug
     * @return Site|null
     */
    public function find($slug)
    {
        $this->load();

        $data = isset($this->sites[$slug]) ? $this->sites[$slug] : null;

        if ($data) {
            return Site::create($slug, $data);
        }

        return null;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return iterator_to_array($this->all());
    }

    /**
     * @param Site $site
     */
    public function store(Site $site)
    {
        $this->load();
        $this->sites[$site->getSlug()] = $site->toArray();
        $this->write();
    }

    /**
     * @param Site|string $site
     */
    public function remove($site)
    {
        $slug = ($site instanceof Site) ? $site->getSlug() : $site;

        $this->load();
        unset($this->sites[$slug]);
        $this->write();
    }

    /**
     * @return Generator
     */
    public function all()
    {
        $this->load();

        foreach ($this->sites as $slug => $values) {
            yield $slug => Site::create($slug, $values);
        }
    }

    /**
     * @return void
     */
    private function load()
    {
        if (is_array($this->sites)) {
            return;
        }

        $data = $this->disk->exists('sites.json') ? $this->disk->get('sites.json') : '';
        $this->sites = (array) json_decode($data, true);
    }

    /**
     * @return void
     */
    private function write()
    {
        if (!is_array($this->sites)) {
            return;
        }

        $data = json_encode($this->sites, JSON_PRETTY_PRINT);
        $this->disk->put('sites.json', $data);
    }
}
