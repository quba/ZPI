<?php

namespace Zpi\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/*
 * Zakładam, że to będzie kontroler skonfigurowany jako serwis po to, by móc tutaj wrzucać jakieś globalne funkcje.
 * Jestem pewien, że można to zrobić bardziej elegancko.
 * Jakby nie użytwać go jako serwis, to zapewne wywołanie go wymagałoby napisania większej ilości kodu (use + wywołanie).
 */


class OverallController extends Controller
{
    // na razie zostawiam, może komuś się kiedyś przyda, żeby wrzucić tu jakąś globalną funkcję
}
