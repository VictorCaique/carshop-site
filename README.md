# CarShopSite — ambiente de desenvolvimento

Template WordPress para lojas de veículos, conforme o `SPEC.md`.
Um **tema** + um **plugin** próprios, versionados, rodando num ambiente Docker replicável.

```
CarShopSite/
├── docker-compose.yml        WordPress + MariaDB + WP-CLI + phpMyAdmin + Mailpit
├── Makefile                  atalhos (make up, make install, make seed…)
├── docker/
│   ├── setup.sh              instalação automática do WP (roda no container)
│   └── uploads.ini           limites de upload do PHP
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

## Campos: zero plugin pago

O SPEC previa ACF PRO. Este projeto usa a alternativa gratuita, dividida em duas partes:

**Campos do veículo → [Meta Box](https://wordpress.org/plugins/meta-box/) (grátis, wordpress.org).**
Registrados em PHP pelo filtro `rwmb_meta_boxes`, em `inc/campos-metabox.php`. A galeria usa
`image_advanced`, que dá múltiplas imagens de graça — era exatamente esse o recurso que
justificava o ACF PRO. O `make install` instala o Meta Box sozinho; não há licença nem zip
para providenciar.

**Configurações do Site → tela nossa, em WordPress nativo.**
Options page é a única peça que o Meta Box cobra (MB Settings Page / AIO), então `inc/opcoes-loja.php`
monta a tela com a Settings API: abas, media picker e color picker do próprio WordPress, e
repeaters em JS próprio (arrastar para reordenar incluso). Tudo grava num único option, `lv_opcoes`.

O `inc/opcoes-schema.php` é a fonte única da verdade dessa tela: a renderização, a sanitização e
os valores padrão saem todos dele. Campo novo nas configurações = **uma entrada nesse array**,
mais nada.

> Trocar de camada de campos continua barato: o tema nunca lê meta direto, sempre passa por
> `lv_field()` / `lv_option()`. E como o Meta Box grava em post meta padrão, se ele for
> desativado o site inteiro continua de pé — só a tela de cadastro do veículo fica sem os campos.

---

## Como o código está organizado

**O dado mora no plugin.** Se um dia o tema for trocado, o estoque do cliente não some —
regra de ouro no WordPress.

| Arquivo | O que faz |
|---|---|
| `inc/cpt-veiculo.php` | CPT `veiculo`, tamanhos de imagem, `alt` automático |
| `inc/taxonomias.php` | marca, carroceria, câmbio, combustível, opcional + termos padrão |
| `inc/campos-metabox.php` | campos do veículo declarados em PHP (nada de configurar campo a campo por site) |
| `inc/opcoes-schema.php` | esquema das *Configurações do Site* — fonte única de campos, padrões e sanitização |
| `inc/opcoes-loja.php` | a tela em si (Settings API, abas, media picker, repeaters) — o coração da replicabilidade |
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
- Sem jQuery no front, sem page builder, sem plugin pago — CSS e JS próprios

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
