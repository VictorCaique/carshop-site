# CarShopSite — ambiente de desenvolvimento

Template WordPress para lojas de veículos, conforme o `SPEC.md`.
Um **tema** + um **plugin** próprios, versionados, rodando num ambiente Docker replicável.

```
CarShopSite/
├── docker-compose.yml        WordPress + MariaDB + WP-CLI + phpMyAdmin + Mailpit
├── Makefile                  atalhos (make up, make install, make seed…)
├── docker/
│   ├── setup.sh              instalação automática do WP (roda no container)
│   ├── uploads.ini           limites de upload do PHP
│   └── acf-pro/              coloque aqui o advanced-custom-fields-pro.zip
├── plugin-lv-estoque/        O DADO — CPT, taxonomias, campos, filtros, schema
│   ├── lv-estoque.php
│   ├── inc/
│   └── seeds/                marcas + 6 veículos de demonstração
├── tema-lv/                  A APARÊNCIA — tema clássico, sem page builder
│   ├── functions.php · front-page.php · archive-veiculo.php · single-veiculo.php
│   ├── inc/ · template-parts/ · assets/
└── deploy/                   instalação de uma loja real em hospedagem
    ├── setup-loja.sh
    ├── loja.env.example
    └── checklist.md
```

---

## Subindo o ambiente

Requisitos: Docker rodando (Docker Desktop, Rancher Desktop ou Colima) e `make`.

```bash
cd ~/Projects/CarShopSite

make up        # sobe os containers
make install   # instala o WordPress, ativa tema e plugin, cria as páginas
make seed      # 6 carros de demonstração com fotos geradas
```

| Serviço | Endereço | Acesso |
|---|---|---|
| Site | http://localhost:8080 | — |
| Admin | http://localhost:8080/wp-admin | `admin` / `admin` |
| phpMyAdmin | http://localhost:8081 | `wp` / `wp` |
| Mailpit (e-mails de teste) | http://localhost:8025 | — |

As portas e senhas ficam no `.env` (criado a partir do `.env.example` no primeiro `make up`).

### Comandos do dia a dia

```bash
make logs                       # logs do WordPress
make wp CMD="plugin list"       # qualquer comando WP-CLI
make lint                       # php -l em todo o tema e plugin
make shell                      # bash no container do WordPress
make down                       # para tudo
make reset                      # APAGA banco e core, e reinstala do zero
```

O tema e o plugin são **bind mounts**: editar um arquivo aqui reflete no site no próximo reload,
sem rebuild.

---

## ACF PRO

O SPEC usa ACF PRO (licença única, sites ilimitados) por causa de **galeria**, **repeater**
e **options page** — os três recursos que o template depende.

Coloque o zip em `docker/acf-pro/` e rode `make install`: o setup detecta e instala.
Sem o zip, o setup instala o ACF free e o site continua de pé, mas a tela
*Configurações do Site* e a galeria não aparecem.

> Alternativa gratuita: Meta Box Lite (o campo `image_advanced` dá múltiplas imagens de graça).
> Nesse caso só a camada de registro de campos muda — o resto do código continua igual, porque
> o tema nunca chama `get_field()` direto: ele passa por `lv_field()` / `lv_option()`.

---

## Como o código está organizado

**O dado mora no plugin.** Se um dia o tema for trocado, o estoque do cliente não some —
regra de ouro no WordPress.

| Arquivo | O que faz |
|---|---|
| `inc/cpt-veiculo.php` | CPT `veiculo`, tamanhos de imagem, `alt` automático |
| `inc/taxonomias.php` | marca, carroceria, câmbio, combustível, opcional + termos padrão |
| `inc/campos-acf.php` | field group declarado em PHP (nada de configurar campo a campo por site) |
| `inc/opcoes-loja.php` | *Configurações do Site* — o coração da replicabilidade |
| `inc/query-filtros.php` | filtros por GET via `pre_get_posts`, ordenação, relacionados |
| `inc/schema.php` | JSON-LD `Car` + `AutoDealer`, Open Graph, favicon, analytics |
| `inc/helpers.php` | `lv_preco()`, `lv_whatsapp_link()`, `lv_km()`… tudo que o tema chama |
| `inc/admin-colunas.php` | listagem de estoque no admin com foto, preço, KM e status |

**A aparência mora no tema**, e nenhuma cor literal existe no CSS: os tokens vêm de
`inc/customizacao.php`, alimentados pelas Configurações do Site. Trocar 3 cores + logo = loja nova.

---

## O que já está implementado

- Vitrine com filtros por GET (URL compartilhável e indexável), ordenação e paginação de 12
- Ficha do veículo com galeria (setas, thumbs, teclado, swipe), ficha técnica, opcionais e relacionados
- CTA de WhatsApp com **mensagem pré-preenchida** e vendedor responsável por veículo
- Simulador de financiamento simples que envia a estimativa pelo WhatsApp
- Status `vendido`: sai da vitrine, mantém a URL viva com selo e CTA de similares
- Home completa: hero com busca rápida, destaques, diferenciais, sobre, depoimentos, contato
- JSON-LD, Open Graph, formulário de contato com consentimento (LGPD) e honeypot
- Sem jQuery no front, sem page builder, CSS e JS próprios

## Fora de escopo na v1 (SPEC §1)

Importação automática de estoque (OLX/Webmotors/DMS), financiamento com retorno real de banco,
checkout, multi-loja e login de cliente final.

---

## Levando para uma loja real

O `deploy/` tem o caminho de produção: `setup-loja.sh` faz a instalação em hospedagem
e `checklist.md` é a lista de aceite das 5 fases do SPEC §10.

Atualizar o template em todas as lojas é `git pull` em cada site — por isso **nunca**
personalize o código do tema por cliente. Se um cliente precisa de algo único, isso vai
num `custom.css` / `custom.php` isolado.
