<?php

namespace yCrawler;

interface Config
{
    public function getUrlsFile();

    public function getRootUrl();

    public function getParser();
}