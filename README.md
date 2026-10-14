# Workshop AI – Symfony + Claude

Seminarie-project: een chatapp die laag per laag slimmer en daarna veiliger wordt.
Elke laag is een git tag; `git checkout laag-1-chat` brengt je naar het einde van die laag.

| Laag | Tag | Onderwerp |
|------|-----|-----------|
| 0 | `laag-0-setup` | Project, API key, model als parameter |
| 1 | `laag-1-chat` | Chat: system prompt, geschiedenis in de sessie |
| 2 | `laag-2-memory` | Geheugen: gesprekken in de database, memory tool |
| 3 | `laag-3-tools` | Tools: het model mag code van ons oproepen |
| 4 | `laag-4-compression` | Tokens, caching, compaction |
| 5 | `laag-5-data-safety` | Prompt injection, logging, wat verlaat je server |
| 6 | `laag-6-gdpr` | Wissen, bewaartermijn, transparantie |

## Zelf draaien

Vereist: PHP 8.4+, Composer, en een Anthropic API key (krijg je tijdens het seminarie).

```bash
composer install
cp .env .env.local          # en vul ANTHROPIC_API_KEY in
symfony serve -d            # of: php -S 127.0.0.1:8000 -t public
```

Open daarna http://127.0.0.1:8000. De database is SQLite en wordt automatisch aangemaakt in `var/`.

### Model wisselen

Het model is een env-variabele. Zet in `.env.local` bijvoorbeeld:

```
CLAUDE_MODEL=claude-sonnet-5-5
```

Beschikbare modellen en prijzen: https://docs.anthropic.com/en/docs/about-claude/models

## Kwaliteitschecks

```bash
composer csfixer   # code style (php-cs-fixer, Symfony-regels)
composer phpstan   # statische analyse
composer test      # phpunit
```

## Waar zit wat

- `src/Ai/` – het gesprek met het model: `ChatModel` (interface), `ClaudeChatModel` (de SDK-call)
- `src/Chat/` – de gesprekslogica en waar de geschiedenis leeft (`ConversationStore`)
- `src/Controller/ChatController.php` – dunne controller: formulier in, redirect uit
- `templates/chat/` – de UI, met per antwoord het tokenverbruik
- `tests/` – `FakeChatModel` vervangt Claude, zodat tests geen key of netwerk nodig hebben
