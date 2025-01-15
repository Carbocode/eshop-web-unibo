# ElPrimeroFootballer: E-Shop di Magliette da Calcio

Questo repository contiene un monorepo per **ElPrimeroFootballer**, un progetto sviluppato come simulazione di un sito di e-commerce per la vendita di magliette da calcio. Il progetto è stato realizzato per l'esame di sviluppo web presso l'Università di Bologna (Unibo).

- vincenzo.prisco@studio.unibo.it
- sohail.mama@studio.unibo.it
- yosbero.baro@studio.unibo.it

## Struttura del progetto

```
.
├── backend/              # Contiene il codice del backend scritto in PHP
│   ├── .well-known/      # Directory per file di configurazione standard
│   ├── db/               # File necessari per la gestione del database SQL
│   ├── src/              # Codice sorgente del backend
│   ├── vendor/           # Dipendenze gestite da Composer
│   ├── composer.json
│   └── composer.lock
├── frontend/             # Contiene il codice del frontend sviluppato in HTML/JS/CSS
│   ├── .well-known/      # Directory per file di configurazione standard
│   ├── dist/             # File generati dalla build di produzione
│   ├── node_modules/     # Dipendenze gestite da npm
│   ├── src/              # Codice sorgente del frontend
│   ├── index.html        # File principale HTML
│   ├── package.json
│   ├── package-lock.json
│   └── vite.config.js    # File di configurazione per Vite
├── .gitattributes
├── .gitignore
├── README.md
```

### Frontend

Il frontend del progetto si trova nella cartella `frontend/` ed è basato su puro HTML, CSS (compilato da SCSS) e JavaScript. Utilizziamo **Vite**, che non è un framework, ma uno strumento per velocizzare lo sviluppo grazie al rendering in tempo reale e alla compilazione automatica dei file SCSS. Tuttavia, Vite non offre vantaggi pratici nella programmazione stessa, servendo solo come ausilio per uno sviluppo più rapido.

#### Strumenti e Dipendenze

- **Vite**: strumento di sviluppo per il rendering in tempo reale e la compilazione di SCSS.
- **FontAwesome**: libreria di icone per migliorare l'interfaccia utente.

#### Installazione

1. Spostati nella directory `frontend/`:
   ```bash
   cd frontend
   ```
2. Installa le dipendenze con npm:
   ```bash
   npm install
   ```

### Backend

Il backend si trova nella directory `backend/` ed è scritto in puro PHP. Utilizziamo **Composer** per la gestione delle dipendenze e la libreria **Firebase JWT** per implementare l'autenticazione tramite JSON Web Token. Inoltre, il backend include i file necessari per la gestione di un database SQL.

#### Strumenti e Dipendenze

- **Composer**: gestore di pacchetti PHP.
- **Firebase JWT**: libreria per la gestione dei token JWT.

#### Installazione

1. Spostati nella directory `backend/`:
   ```bash
   cd backend
   ```
2. Installa le dipendenze con Composer:
   ```bash
   composer install
   ```

#### Funzionalità Implementate

- **Login e Autenticazione**: il backend gestisce l'autenticazione degli utenti utilizzando JWT.
- **API RESTful**: fornisce endpoint per comunicare con il frontend.

## Avvio del Progetto

1. Entra nella directory `frontend/`, installa le dipendenze e avvia il server di sviluppo:
   ```bash
   cd frontend
   npm install
   npm run dev
   ```
2. Entra nella directory `backend/`, installa le dipendenze e avvia il server PHP:
   ```bash
   cd backend
   composer install
   php -S 127.0.0.1:8000
   ```
3. Avvia il database MariaDB sulla porta `3306` tramite XAMPP o installandolo separatamente.
4. Inizializza il database e aggiorna le credenziali nel file `preflight` prima di utilizzare il progetto.
