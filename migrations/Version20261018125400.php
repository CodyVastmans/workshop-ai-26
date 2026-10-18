<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Een handvol games om vanaf laag 3 in te zoeken. Data-migration, zodat één migrate-commando volstaat.
 */
final class Version20261018125400 extends AbstractMigration
{
    private const array GAMES = [
        ['Rocket League', 'sport', 1, 4, 0.0, 'PC, PS, Xbox, Switch', true,
            'Voetbal met raketauto\'s. Gratis, snel en perfect in teams van 2 of 3.'],
        ['Among Us', 'party', 4, 15, 4.49, 'PC, Switch, mobiel', false,
            'Zoek de bedrieger in je crew. Werkt het best met een grote groep en een voice chat.'],
        ['Lethal Company', 'horror', 1, 4, 9.75, 'PC', true,
            'Verzamel schroot op verlaten manen terwijl monsters je opjagen. Coop-horror om samen te gillen.'],
        ['Portal 2', 'puzzle', 1, 2, 9.99, 'PC, PS, Xbox', true,
            'Portaalpuzzels met een aparte coop-campagne voor twee spelers.'],
        ['Stardew Valley', 'simulatie', 1, 4, 13.99, 'PC, Switch, PS, Xbox, mobiel', true,
            'Rustige boerderijsim die je met maximum vier vrienden kunt delen.'],
        ['Phasmophobia', 'horror', 1, 4, 19.99, 'PC, PS, VR', true,
            'Spookjagen met vier man: bewijs verzamelen terwijl het spook jullie uitlokt.'],
        ['Overcooked! 2', 'party', 1, 4, 24.99, 'PC, Switch, PS, Xbox', true,
            'Chaotisch samen koken. Vriendschappen worden getest.'],
        ['Hades', 'roguelike', 1, 1, 24.99, 'PC, Switch, PS, Xbox', false,
            'Ontsnap uit de onderwereld. Solo, strak en verslavend.'],
        ['Jackbox Party Pack 9', 'party', 1, 8, 29.99, 'PC, Switch, PS, Xbox', false,
            'Partyspellen waarbij iedereen meespeelt op zijn eigen smartphone.'],
        ['Minecraft', 'sandbox', 1, 8, 29.99, 'PC, Switch, PS, Xbox, mobiel', true,
            'Bouwen, overleven en samen een wereld maken.'],
        ['It Takes Two', 'avontuur', 2, 2, 39.99, 'PC, PS, Xbox, Switch', true,
            'Alleen met twee te spelen, elke level een ander spelmechanisme.'],
        ['Mario Kart 8 Deluxe', 'racing', 1, 4, 59.99, 'Switch', false,
            'De klassieker voor op de bank. Tot vier spelers op één scherm.'],
        ['Baldur\'s Gate 3', 'rpg', 1, 4, 59.99, 'PC, PS, Xbox', true,
            'Enorme D&D-rpg die je met vier spelers in coop kunt doorlopen.'],
        ['Elden Ring', 'rpg', 1, 1, 59.99, 'PC, PS, Xbox', false,
            'Open wereld, hoge moeilijkheidsgraad, beperkte coop. Voor wie graag afziet.'],
        ['Valorant', 'shooter', 5, 10, 0.0, 'PC', true,
            'Tactische 5-tegen-5 shooter. Gratis, maar vraagt een vast team.'],
        ['Fall Guys', 'party', 1, 4, 0.0, 'PC, PS, Xbox, Switch', false,
            'Zestig bonen in een hindernisparcours. Gratis en laagdrempelig.'],
    ];

    public function getDescription(): string
    {
        return 'Vult de gamebibliotheek met voorbeelddata';
    }

    public function up(Schema $schema): void
    {
        foreach (self::GAMES as [$title, $genre, $min, $max, $price, $platforms, $coop, $description]) {
            $this->addSql(
                'INSERT INTO game (title, genre, min_players, max_players, price, platforms, coop, description) '
                .'VALUES (:title, :genre, :min, :max, :price, :platforms, :coop, :description)',
                [
                    'title' => $title, 'genre' => $genre, 'min' => $min, 'max' => $max, 'price' => $price,
                    'platforms' => $platforms, 'coop' => (int) $coop, 'description' => $description,
                ],
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM game');
    }
}
