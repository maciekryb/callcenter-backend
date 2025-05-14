# System Zarządzania Grafikiem (Backend)

## Opis projektu

System do zarządzania grafikami agentów Call Center w firmie telekomunikacyjnej. Pozwala na optymalne układanie grafików pracy agentów w oparciu o:
- prognozy zapotrzebowania na połączenia w poszczególnych kolejkach,
- indywidualne umiejętności i wydajność agentów (liczba obsługiwanych połączeń na godzinę),
- dostępność agentów (pełny dzień, część dnia, niedostępność),
- elastyczne godziny pracy i obsługę wielu kolejek przez jednego agenta.

Projekt zawiera backend (Laravel) oraz frontend (osobny projekt – patrz niżej).

---

## Wymagania

- PHP >= 8.1
- Composer
- MySQL lub inna baza obsługiwana przez Laravel
- Node.js (jeśli chcesz uruchomić frontend)

---

## Instalacja backendu (Laravel)

1. **Klonowanie repozytorium**

   ```bash
   # Skopiuj repozytorium do wybranego katalogu
   git clone <adres-repo>
   cd callcenter-backend
   ```

2. **Instalacja zależności**

   ```bash
   composer install
   ```

3. **Konfiguracja środowiska**

   Skopiuj plik `.env.example` do `.env` i ustaw dane dostępowe do bazy:

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Ustaw w `.env`:
   ```
   DB_DATABASE=callcenter
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

4. **Migracje**

   Uruchom migracje, aby utworzyć wszystkie tabele w bazie danych:

   ```bash
   php artisan migrate
   ```

5. **Seedy (przykładowe dane)**

   Seedy wypełniają bazę przykładowymi danymi:
   - Tworzą 4 kolejki tematyczne (np. sprzedaż, do sprzedaż, wsparcie techniczne, ogólna).
   - Tworzą przykładowe prognozy obciążenia (liczba połączeń na godzinę dla każdej kolejki).
   - Tworzą 20 przykładowych agentów (pracowników call center).
   - Przypisują agentów do kolejek wraz z ich wydajnością (liczba połączeń/h).
   - Generują przykładowe dostępności agentów na najbliższe dni (pełny dzień, zakres godzin, niedostępność).

   *Na potrzeby testowe dane zawsze są generowane w ujęciu bieżącego tygodnia*

   Aby uruchomić seedy:

   ```bash
   php artisan db:seed
   ```


6. **Uruchomienie serwera developerskiego**

   ```bash
   php artisan serve
   ```

   Backend będzie dostępny domyślnie pod adresem: http://127.0.0.1:8000

---

## API

Stworzone endpointy (patrz `routes/api.php`).

*Uwaga: W celach demonstracyjnych wszystkie operacje dotyczą danych w ujęciu jednego tygodnia*

- `POST /api/agents`  
  Tworzy nowego agenta wraz z przypisaniem do kolejek i wydajnością.
- `GET /api/queues`  
  Zwraca listę wszystkich kolejek tematycznych.
- `GET /api/queue/{id}/agents-schedule`  
  Zwraca grafik dostępności agentów dla wybranej kolejki (na podstawie dostępności, nie docelowego grafiku pracy) – dla całego tygodnia.
- `GET /api/queue/{id}/work-load`  
  Zwraca prognozę obciążenia (liczbę połączeń na godzinę) dla wybranej kolejki – dla całego tygodnia.
- `GET /api/work-schedule/queue/{id}`  
  Zwraca docelowy grafik pracy agentów dla wybranej kolejki (na podstawie wygenerowanego harmonogramu) – dla całego tygodnia.
- `POST /api/work-schedule/generate`  
  Generuje grafik pracy agentów na podstawie prognoz, dostępności i wydajności (uruchamia algorytm optymalizujący grafik) – dla całego tygodnia.

---

## Frontend

Projekt frontendowy znajduje się w osobnym repozytorium i został zbudowany w technologii **React + Vite + Tailwind CSS**.
- **Repozytorium:** [https://github.com/maciekryb/callcenter-frontend](https://github.com/maciekryb/callcenter-frontend)
- **Instrukcja uruchomienia:** patrz README w repozytorium frontendu

Frontend komunikuje się z backendem przez powyższe endpointy REST API.

---

## Najważniejsze założenia i logika

- Agenci mogą obsługiwać wiele kolejek, każda z indywidualną wydajnością (liczba połączeń/h).
- System generuje grafik na podstawie prognoz i dostępności agentów, optymalizując pokrycie zapotrzebowania.
- Algorytm najpierw obsadza godziny z największym ruchem.
- Przykładowe dane generowane są przez seedery.

---

## Uruchomienie całości (backend + frontend)

1. Uruchom backend według powyższej instrukcji.
2. Uruchom frontend według instrukcji w repozytorium frontendu.
3. Upewnij się, że adresy API w frontendzie wskazują na backend (np. http://localhost:8000/api).

