# Translation Palace 🏰

A self‑hosted, local‑only translator that **remembers** your corrections, **learns** your domain‑specific slang, and **retrieves** the right context every time – all powered by a small, offline‑friendly LLM.
---

## ✨ Features

- **Memory‑Palace Architecture** – Glossary, Idioms, Translation Memory, and Difficult Cases are organised into **Domain → Wing → Room** drawers.
- **RAG + GraphRAG** – Every translation is augmented with:
  - Exact glossary matches (with context‑priority overrides)
  - Vector‑search results (`pgvector`) of your previously approved translations
  - Difficult‑case patterns (phrases you’ve manually explained)
- **Golden Memories** – Mark translations as “gold” to lock in the highest‑quality examples.
- **Full‑Featured Admin Panel** – Manage all memory types with search, pagination, modals, and a cascading hierarchy selector.
- **Dark Theme** – Molten Rock colour palette (other themes are available in `resources/css/app.css`).
- **100% Local** – No API keys, no cloud. Your data stays on your machine.
---

## 🧰 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, PHP 8.3+ |
| Frontend | Livewire 4, Alpine.js, Tailwind CSS v4 (Vite) |
| Database | PostgreSQL 15+ with `pgvector` extension |
| LLM | [Ollama](https://ollama.com) (qwen2.5-coder:3b recommended) |
| Embeddings | Ollama (`nomic-embed-text`) |
| LLM client | [Prism](https://prism.echolabs.dev/) for Laravel |
| Vector search | `pgvector/pgvector` Composer package |
---

## 📦 Installation

### 1. Clone the repository

```bash
git clone https://github.com/cer3us/translation-palace.git
cd translation-palace
```
### 2. Install dependencies
```bash
composer install
npm install
```
### 3. Environment setup
```bash
cp .env.example .env
# edit .env

php artisan key:generate
```
### 4. Create the database
### 5. Enable PostgreSQL extensions
```bash
CREATE EXTENSION IF NOT EXISTS vector;
```
### 6. Run migrations
```bash
php artisan migrate
```
### 7. Install & pull Ollama models
```bash
ollama pull qwen2.5-coder:3b
ollama pull nomic-embed-text
```
- 💡 You can use any Ollama model. The default is configured in `config/translation.php`.
### 8. Build frontend assets
```bash
npm run build   # for production
# or
npm run dev     # for development
```
---

⚙️ Configuration
All important settings are in the `config/ directory`:
`translation.php` – languages, LLM model, embedding model, vector threshold.
`palace.php` – available domains, wings, rooms, and tags.
Edit these files to match your workflow and the languages you translate between.
---

🧠 How the Memory Palace Works
Glossary Atrium – Exact term matches (e.g., “middleware” → “промежуточный слой” in a Laravel context).

Idiom Gallery – Vector search over your previously translated sentences (few‑shot examples).

Difficult Cases Library – Stored patterns with explanations (e.g., “it hit the fan” → “всё пошло наперекосяк”).

Vector Threshold – Only memories with a cosine similarity better than the configured threshold are used (default 0.4).

This ensures that even a small local model (2‑4B parameters) produces high‑quality, style‑consistent translations.
---

🤝 Acknowledgements
- [MemPalace]{https://github.com/mempalace/mempalace} for the memory‑palace inspiration.
- [Laravel](https://laravel.com/) and [Livewire](https://livewire.laravel.com/) for the developer experience.
- [Prism](https://prismphp.com/) for a unified Laravel LLM interface.
- [PGVector](https://github.com/pgvector/pgvector) for bringing vectors to PostgreSQL.
- [Ollama](https://ollama.com/) for making llms accessible to the crowd.
---

📄 License
This project is open‑sourced under the MIT license.
---

