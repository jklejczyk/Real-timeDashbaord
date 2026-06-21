# Realtime Dashboard

Edukacyjny dashboard zleceń serwisowych w czasie rzeczywistym. Projekt pokazuje pełny pipeline broadcastingu Laravela (event → queue → Reverb → Echo → Vue) oraz pięć wzorców „WS-natywnych": live feed, presence, client events (whisper), notyfikacje broadcastowe i odporność połączenia.

## Stack

- **Backend:** Laravel 13 (PHP 8.4), Laravel Reverb (WebSocket server), Fortify (auth)
- **Frontend:** Inertia v3 + Vue 3, Tailwind v4, Chart.js, `@laravel/echo-vue`, vue-sonner (toasty)
- **Baza/cache/queue:** PostgreSQL, Redis (cache + kolejka broadcastu)
- **Dev env:** Laravel Sail (Docker), Vite
- **Jakość:** Pest 4, Larastan (phpstan level 7), Pint

## Architektura

### Przepływ danych (odczyt dashboardu)

Łańcuch: **DashboardController → StatsServiceInterface → CachedStatsService (cache Redis) → StatsService → DB**, a wynik wraca jako propsy Inertii do `Dashboard.vue`.

- `DashboardController` przyjmuje filtry przez `DashboardFilterRequest` → DTO `DashboardFilters`.
- `StatsServiceInterface` jest związany w `AppServiceProvider` z `CachedStatsService`, który **dekoruje** `StatsService` warstwą cache. Serwis liczy agregaty (przychód, statusy, top pracownicy, trendy) — **single source of truth**.
- Cięższe wykresy (`revenuePerMonth`, `ordersTrend`) lecą jako **deferred props** Inertii (skeleton → doładowanie).

### Pipeline realtime (zapis + broadcast)

Łańcuch: **utworzenie zlecenia → `Cache::flush()` → `OrderCreated::dispatch()` → queue (Redis) → Reverb → Echo (przeglądarka)**.

Na froncie ten sam event obsługują dwa composable:

- `useLiveDashboard` — traktuje event jak **chudy sygnał** i robi `router.reload({ only })` po świeże agregaty.
- `useActivityFeed` — czyta **gruby payload** i robi `items.unshift(wiersz)` bez round-tripu.

> **Triada zapisu:** PERSIST → INVALIDATE cache → BROADCAST. Pominięcie invalidacji = `router.reload` oddaje stary cache.

## Wzorce realtime (kluczowa część projektu)

| Wzorzec | Co robi | Backend | Frontend |
|---|---|---|---|
| **Live feed** | gruby payload niesie cały wiersz, front akumuluje bez round-tripu | `OrderCreated` (`broadcastWith`) | `composables/useActivityFeed.ts` |
| **Agregaty live** | chudy sygnał → `router.reload({ only })` po świeże sumy | `OrderCreated` | `composables/useLiveDashboard.ts` |
| **Presence** | „kto ogląda" — auth zwraca **dane** usera, nie bool | `routes/channels.php` (`dashboard`) | `composables/usePresence.ts` |
| **Whisper** | client→client „X ogląda widget", omija backend | — (zero PHP) | `usePresence.ts` (`whisper`/`listenForWhisper`) |
| **Alert** | Notification na kanale `broadcast`, per-user | `Notifications/DelayedOrdersAlert` + `orders:check-alerts` | `composables/useDashboardAlerts.ts` → toast |
| **Resilience** | baner „utracono połączenie" + resync po reconnect | — | `composables/useConnectionResilience.ts` |

## Uruchomienie lokalne

```bash
# 1. Zależności + env
composer install && npm install
cp .env.example .env && php artisan key:generate

# 2. Kontenery (app, pgsql, redis, queue, reverb) — Reverb słucha na 8081
./vendor/bin/sail up -d

# 3. Migracje + seed demo
./vendor/bin/sail artisan migrate:fresh --seed

# 4. Front (Vite)
./vendor/bin/sail npm run dev
```

Dashboard: `http://localhost:8080/dashboard`. Reverb (queue + WS) wstaje razem z `sail up` jako osobne kontenery.

## Konta demo

| email | hasło | po co |
|---|---|---|
| `test@example.com` | `demo123` | główne |
| `test2@example.com` | `demo123` | drugie okno (presence/whisper) |

## Demo na żywo

```bash
# Ciągła symulacja: co kilka sekund nowe zlecenie → live feed + agregaty + okazjonalny alert
./vendor/bin/sail artisan demo:simulate            # interwał 3 s
./vendor/bin/sail artisan demo:simulate --interval=1

# Pojedyncze zdarzenie
./vendor/bin/sail artisan order:simulate

# Ręczne sprawdzenie progu opóźnionych → alert (toast)
./vendor/bin/sail artisan orders:check-alerts
```

Otwórz `/dashboard`, odpal `demo:simulate` i patrz: timeline rośnie, liczniki/wykresy się aktualizują, a gdy wpadnie opóźnione zlecenie — toast w prawym górnym rogu. Presence/whisper zobaczysz logując oba konta w dwóch oknach.

## Testy

```bash
./vendor/bin/sail test --compact          # cały zestaw (Postgres w kontenerze)
./vendor/bin/sail test --filter=Dashboard # filtr
vendor/bin/phpstan analyse                # analiza statyczna (level 7)
vendor/bin/pint                           # formatowanie
```
