<?php

namespace Bryanjamesmiller\LaravelInky;

use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Compilers\CompilerInterface;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\DomCrawler\Crawler;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class InkyCompilerEngine extends CompilerEngine
{
    protected $files;

    public function __construct(CompilerInterface $compiler, Filesystem $files)
    {
        parent::__construct($compiler);
        $this->files = $files;
    }

    public function get($inkyFilePath, array $data = [])
    {
        // Compiles the inky template as if it were a regular blade file
        $html = parent::get($inkyFilePath, $data);

        // remove css stylesheet links from email's HTML
        $crawler = new Crawler();
        $crawler->addHtmlContent($html);
        $cssLinks = $crawler->filter('link[rel=stylesheet]');

        $cssLinks->each(function (Crawler $crawler) {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $htmlWithoutLinks = $crawler->html();

        // this array of CSS files to be used in the email will be
        // provided via a publishable config file
        $stylesheetsHrefs = collect([
            'css/foundation-emails.css'
        ]);

        // combines all stylesheets in the config file into 1 string of CSS
        $styles = $stylesheetsHrefs->map(function ($path) {
            // $stylesheetsHrefs will be an array of all css files to be
            // included in the emails.  They will be entered in a publishable config file
            // by users and will need to live at /public/$path

            // desired output => css/foundation-emails.css
            // (this path successfully references a file in the public/css folder)
            return $this->files->get($path);
        })->implode("\n\n");

        $inliner = new CssToInlineStyles();
        return $inliner->convert($htmlWithoutLinks, $styles);
    }

    public function getFiles()
    {
        return $this->files;
    }
}