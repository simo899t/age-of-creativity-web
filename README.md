# Age of Creativity website

WordPress block theme for the Age of Creativity research project.

- **Front page:** an interactive timeline of creators (music, art, literature, science, 1400–1950). Each line is a life from birth to death; hovering shows who it is and their connections, clicking pins a card with details, and the field buttons and search narrow it down.
- **Everything else:** ordinary WordPress pages built with blocks, so anyone can edit them. The theme ships ready-made sections (team grid, publication list, report download, event, contact, highlighted note, text next to image) under the *Age of Creativity* pattern category.

How to edit the site without touching code: [docs/editing-guide.md](docs/editing-guide.md).

## Preview in the browser

`blueprint.json` boots the theme from this repo's `main` branch in [WordPress Playground](https://wordpress.org/playground/), with the starter pages, menu and creators, and logs you in as admin. Nothing is saved between visits.

- With GitHub Pages turned on (Settings → Pages → Deploy from a branch → `main`, `/ (root)`), `index.html` shows it at `https://simo899t.github.io/age-of-creativity-web/`.
- Or open `https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/simo899t/age-of-creativity-web/main/blueprint.json` directly.

Both need the repository to be public, because Playground downloads the theme from GitHub.

## Run it locally

```sh
docker compose up -d
```

Open http://localhost:8080, run the WordPress installer, then go to **Appearance → Themes** and activate **Age of Creativity**. On activation the theme adds anything that's missing:

- the pages Home, About, Team, Output (Database, Publications, Reports), Events and Contact
- the main menu
- the four fields and 52 example creators for the timeline
- Home as the static front page, plus site title and tagline if they are still WordPress defaults

Nothing that already exists is overwritten. The same step can be re-run from **Appearance → Starter content**.

## Where things live

| Path | What |
| --- | --- |
| `theme/theme.json` | Colours, fonts, sizes, spacing (also editable in Appearance → Editor → Styles) |
| `theme/templates/`, `theme/parts/` | Page layouts, header and footer |
| `theme/patterns/` | Ready-made sections editors can insert |
| `theme/blocks/timeline/` | The timeline block (`render.php` server output, `view.js` interactivity, `style.css`) |
| `theme/inc/creators.php` | Creators post type, Fields taxonomy (colour + eras), Timeline settings page |
| `theme/inc/starter-content.php` | First-run pages, menu and creators |
| `theme/inc/data/starter-creators.json` | The example creators, taken from the original reference design |
| `theme/assets/fonts/` | Fraunces, Source Serif 4 and Inter (SIL Open Font License), self-hosted |

No build step: the block's JavaScript is plain browser JS.
