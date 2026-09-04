# SPEC.md — Template WordPress para Lojas de Veículos

> Especificação técnica e operacional de um produto replicável: site institucional + vitrine de estoque para lojas de carros de bairro.
>
> Versão: 1.1 · Stack: WordPress 6.5+ / PHP 8.1+ · **Sem plugin pago**

---

## 1. Objetivo

Entregar, em poucas horas de trabalho por cliente, um site que:

1. Dê presença online e credibilidade à loja (hoje ela só existe no WhatsApp e no Instagram).
2. Mostre o estoque atual com fotos e ficha técnica.
3. Converta a visita em uma conversa no WhatsApp com o vendedor.
4. Seja **atualizável pelo próprio dono da loja**, sem depender de você.

O produto não é "um site". É **um template + um processo de implantação**. O que se otimiza aqui é o tempo da segunda loja em diante.

### Fora de escopo (v1)

- Integração/importação automática de estoque de OLX, Webmotors, iCarros ou de DMS.
- Simulação de financiamento com retorno real de banco.
- Checkout/pagamento online.
- Multi-loja (filiais) num mesmo site.
- Área de login para cliente final.

---

## 2. Decisões de arquitetura (e por quê)

| Decisão | Escolha | Motivo |
|---|---|---|
| Onde fica o código | **1 tema + 1 plugin próprios**, versionados em Git | Corrigiu um bug? Sobe em todas as lojas. Com page builder, cada site vira um floco de neve. |
| Page builder | **Não usar** (Elementor/Divi/WPBakery) | Peso, custo de licença por site, e cada cliente "mexendo" quebra o layout. |
| Identidade visual | Variáveis CSS alimentadas por uma tela de opções | Trocar 3 cores + logo = loja nova. Nada de editar arquivo por cliente. |
| Estoque | Custom Post Type `veiculo` dentro do **plugin** (não do tema) | Se um dia trocar o tema, o estoque do cliente não some. Isso é regra de ouro no WordPress. |
| Campos do veículo | **Meta Box** (gratuito, wordpress.org), declarado em PHP via `rwmb_meta_boxes` | O campo `image_advanced` dá galeria de múltiplas imagens de graça — era o único recurso que justificava uma licença. Declarado em código = não precisa configurar campo a campo em cada instalação. |
| Configurações da loja | **Tela nossa**, em WordPress nativo (Settings API) | Options page é a única peça que o Meta Box cobra (MB Settings Page / AIO). Escrever a tela sai mais barato que a licença — e tira a dependência externa justamente da parte mais crítica do template. |
| Acoplamento com a camada de campos | O tema **nunca** lê meta direto: passa por `lv_field()` / `lv_option()` | Trocar de plugin de campos mexe só no registro. E como o Meta Box grava em post meta padrão, se ele for desativado o site continua de pé. |
| Filtros da vitrine | Query nativa via `pre_get_posts` + parâmetros GET | Sem plugin de filtro (FacetWP/Search&Filter). Mais rápido, sem custo recorrente, URLs indexáveis. |
| Tema em blocos (FSE) | **Não.** Tema clássico com templates PHP | Curva de aprendizado menor pra você agora, e controle total do HTML. Migração pra FSE fica pro v2, se fizer sentido. |

**Sobre o ACF PRO (decisão revista na v1.1):** a v1.0 previa ACF PRO por causa de galeria, repeater e options page. O Meta Box gratuito cobre galeria e repeater; a options page virou código nosso. O resultado é o mesmo produto sem licença nenhuma — e uma dependência a menos para gerenciar em N sites.

---

## 3. Stack

- **WordPress** 6.5+ · **PHP** 8.1+ · **MySQL/MariaDB**
- **Plugins obrigatórios em toda instalação:**
  - `meta-box` — campos do veículo (gratuito, wordpress.org)
  - `wordfence` ou `solid-security-basic` — segurança
  - `updraftplus` — backup automático
  - `webp-express` ou similar — conversão de imagem (foto de carro tirada de celular tem 6 MB)
- **Opcionais:** `rank-math` para SEO local (o plugin já emite o JSON-LD do veículo, então Rank Math entra só pelo controle de títulos/sitemap).
- **Ambiente local:** Docker Compose (WordPress + MariaDB + WP-CLI + phpMyAdmin + Mailpit), versionado no repositório.
- **Hospedagem:** compartilhada com PHP 8.1+ e SSL grátis já resolve. Hostinger, KingHost, Hostgator. Para escalar, um VPS com CyberPanel/RunCloud hospedando N lojas sai mais barato por site a partir de ~8 clientes.

---

## 4. Modelo de dados

### 4.1 Custom Post Type: `veiculo`

```
slug público: /veiculos/{marca}-{modelo}-{ano}
supports: title, editor (descrição livre), thumbnail (foto de capa), excerpt
has_archive: true  → /veiculos/
show_in_rest: true
menu_icon: dashicons-car
```

O **título** do post é o nome comercial do carro: `Honda Civic EXL 2.0 2019`.

### 4.2 Taxonomias

| Taxonomia | Slug | Hierárquica | Uso |
|---|---|---|---|
| Marca | `marca` | Não | Filtro principal + página `/marca/honda/` |
| Carroceria | `carroceria` | Não | Hatch, Sedã, SUV, Picape, Utilitário |
| Câmbio | `cambio` | Não | Manual, Automático, CVT, Automatizado |
| Combustível | `combustivel` | Não | Flex, Gasolina, Diesel, Híbrido, Elétrico |
| Opcionais | `opcional` | Não | Ar-condicionado, Direção elétrica, Multimídia, Câmera de ré… |

Marca/carroceria/câmbio/combustível são taxonomia (e não campo de texto) porque geram páginas indexáveis e filtros consistentes — o dono da loja não vai digitar "automatico", "Automático" e "AUT" em três carros diferentes.

### 4.3 Campos do veículo (Meta Box)

Registrados em `plugin-lv-estoque/inc/campos-metabox.php`, pelo filtro `rwmb_meta_boxes`. Divididos em quatro caixas: **Preço**, **Ficha técnica**, **Fotos** (coluna principal) e **Venda** (barra lateral).

| Campo | `id` | Tipo Meta Box | Obrigatório | Observação |
|---|---|---|---|---|
| Preço | `preco` | `number` | Sim | Em reais, sem formatação. Ex: `78900` |
| Preço promocional | `preco_promocional` | `number` | Não | Se preenchido, mostra o original riscado |
| Mostrar preço | `mostrar_preco` | `checkbox` | — | Default: sim. Se não, exibe "Consulte" |
| Ano de fabricação | `ano_fabricacao` | `number` | Sim | 4 dígitos |
| Ano do modelo | `ano_modelo` | `number` | Sim | 4 dígitos |
| Quilometragem | `km` | `number` | Sim | |
| Cor | `cor` | `text` | Sim | |
| Portas | `portas` | `select` (2/4) | Não | |
| Motor | `motor` | `text` | Não | Ex: `1.0 Turbo 12V` |
| Potência | `potencia` | `number` | Não | Em cv |
| Final da placa | `final_placa` | `number` | Não | |
| Único dono | `unico_dono` | `checkbox` | Não | |
| IPVA pago | `ipva_pago` | `checkbox` | Não | |
| Aceita troca | `aceita_troca` | `checkbox` | Não | |
| Galeria | `galeria` | `image_advanced` | Sim | Mín. 4, ideal 8–12 fotos |
| Status | `status_veiculo` | `select` | Sim | `disponivel` \| `reservado` \| `vendido` |
| Destaque na home | `destaque` | `checkbox` | Não | |
| Vendedor responsável | `vendedor` | `select` (opções vindas das configurações) | Não | Define o WhatsApp do botão |

**Como o `image_advanced` grava:** um ID de anexo por linha no post meta, todas com a mesma chave. Ler com `get_post_meta( $id, 'galeria', false )` — o `lv_galeria_ids()` já cuida disso e ainda põe a imagem destacada na frente.

**Regra de status:** veículo `vendido` sai da vitrine mas **não é apagado** — a URL continua viva com um selo "VENDIDO" e um CTA "ver carros similares". Isso preserva SEO e ainda gera lead.

**Conveniência:** se o veículo não tiver imagem destacada ao salvar, a primeira foto da galeria vira a capa automaticamente. O lojista sobe as fotos e pronto.

### 4.4 Configurações da loja (tela nativa)

Este é o coração da replicabilidade. Tudo que muda de cliente para cliente está aqui, num único option `lv_opcoes`.

A tela é nossa, escrita com a Settings API (`inc/opcoes-loja.php`), usando o media picker e o color picker do próprio WordPress. Os repeaters são JS nosso, com reordenação por arrasto.

O **esquema** (`inc/opcoes-schema.php`) é a fonte única da verdade: renderização, sanitização e valores padrão saem todos dele. **Campo novo = uma entrada nesse array.**

Tipos aceitos: `text · textarea · wysiwyg · number · email · url · tel · color · select · checkbox · image · repeater`.
Largura por campo: `meia`, `terco`, `cheia`.

**Aba Identidade**
- `logo` (imagem), `logo_rodape` (versão clara), `favicon`
- `cor_primaria`, `cor_secundaria`, `cor_destaque` (color pickers)
- `fonte` (select entre 3 combinações pré-definidas)

**Aba Contato**
- `nome_loja`, `slogan`, `cnpj`, `ano_fundacao`
- `endereco`, `cidade`, `estado`, `cep`
- `google_maps_embed` (iframe)
- `telefone_fixo`, `email`
- `horario_funcionamento` (repeater: dia + horário)
- `instagram`, `facebook`

**Aba Vendedores** (repeater)
- `nome`, `whatsapp` (só dígitos, formato `5511987654321`), `foto` (opcional), `cargo`

**Aba Conteúdo**
- `hero_titulo`, `hero_subtitulo`, `hero_imagem`
- `sobre_titulo`, `sobre_texto`, `sobre_imagem`
- `diferenciais` (repeater: ícone + título + texto) — ex: "Garantia de 3 meses", "Financiamos em até 60x"
- `depoimentos` (repeater: nome + texto + nota)

**Aba Integrações**
- `google_analytics_id`, `meta_pixel_id`, `google_site_verification`, `whatsapp_flutuante`

---

## 5. Páginas e componentes

### 5.1 Home (`front-page.php`)

Ordem das seções, de cima para baixo:

1. **Header** — logo, menu (Início · Estoque · Sobre · Contato), botão WhatsApp fixo.
2. **Hero** — imagem de fundo (a fachada da loja ou um carro do estoque), título, subtítulo e uma **busca rápida** (marca + faixa de preço + botão "Ver carros").
3. **Destaques** — 6 a 8 cards de veículos com `destaque = true`, carrossel no mobile.
4. **Diferenciais** — 3 ou 4 blocos com ícone (vem das configurações).
5. **Sobre a loja** — texto + foto, com selo de anos de mercado.
6. **Depoimentos** — 3 cards.
7. **Localização e contato** — mapa embutido, endereço, horários, telefone, CTA de WhatsApp.
8. **Footer** — logo, links, redes sociais, CNPJ, "Desenvolvido por [você]" (link discreto — sua melhor fonte de leads).

### 5.2 Vitrine (`archive-veiculo.php`)

- **Barra de filtros** (sidebar no desktop, drawer no mobile): Marca, Carroceria, Câmbio, Combustível, Faixa de preço (min/max), Ano mínimo, KM máxima.
- **Ordenação:** Mais recentes · Menor preço · Maior preço · Menor KM · Mais novo.
- **Grid de cards:** foto de capa, título, ano/ano, KM, câmbio, preço, badge de status.
- **Paginação** de 12 em 12.
- Filtros aplicados via **GET** (`/veiculos/?marca=honda&preco_max=80000`) — URL compartilhável e indexável.
- Estado vazio tratado: "Nenhum carro encontrado com esses filtros" + botão limpar + CTA de WhatsApp "procurar pra mim".

### 5.3 Ficha do veículo (`single-veiculo.php`)

- Galeria em destaque (setas, thumbnails, teclado, swipe no mobile) — JS próprio, sem biblioteca.
- Título, ano, KM, preço grande.
- **CTA principal:** botão WhatsApp com mensagem pré-preenchida (ver §7.3).
- CTAs secundários: "Ligar agora" (`tel:`), "Simular financiamento" (modal que envia a estimativa por WhatsApp).
- Tabela de ficha técnica (todos os campos preenchidos).
- Lista de opcionais em chips.
- Descrição livre (editor).
- Bloco do vendedor: foto, nome, WhatsApp direto.
- **Veículos relacionados:** 4 da mesma marca, completando por faixa de preço.
- Selo "VENDIDO"/"RESERVADO" sobreposto quando aplicável.

### 5.4 Páginas fixas

- **Sobre** — história, equipe, fotos da loja.
- **Contato** — formulário + mapa + horários.
- **Política de Privacidade** — obrigatória (LGPD).
- **404** — sugere ir para a vitrine.

---

## 6. Identidade visual por loja

O tema define tudo em variáveis CSS, injetadas no `<head>` a partir das opções:

```php
// tema-lv/inc/customizacao.php
add_action( 'wp_head', function () {
    $primaria   = (string) ( lv_option( 'cor_primaria' )   ?: '#0B3D91' );
    $secundaria = (string) ( lv_option( 'cor_secundaria' ) ?: '#111827' );
    $destaque   = (string) ( lv_option( 'cor_destaque' )   ?: '#F59E0B' );
    ?>
    <style id="lv-tokens">
      :root{
        --cor-primaria: <?php echo esc_attr( $primaria ); ?>;
        --cor-primaria-escura: <?php echo esc_attr( lv_ajustar_cor( $primaria, -18 ) ); ?>;
        --cor-secundaria: <?php echo esc_attr( $secundaria ); ?>;
        --cor-destaque: <?php echo esc_attr( $destaque ); ?>;
        --cor-texto:#1F2937; --cor-texto-suave:#6B7280;
        --cor-fundo:#FFFFFF; --cor-fundo-alt:#F9FAFB; --cor-borda:#E5E7EB;
        --raio:12px; --sombra:0 1px 3px rgb(0 0 0 / .1), 0 8px 24px rgb(0 0 0 / .06);
        --container:1200px;
      }
    </style>
    <?php
}, 6 );
```

Nenhuma cor literal no CSS do tema. Se você escreveu `#0B3D91` dentro de um `.css`, errou. Até os neutros (`#fff`, cinzas) são tokens — `--cor-branco`, `--cor-inverso`.

O `lv_ajustar_cor()` deriva os estados `:hover` a partir da cor primária, para não pedir mais uma cor ao cliente.

**Presets de fonte** (select nas opções, carregadas via Google Fonts com `display=swap`):

| Preset | Títulos | Corpo | Perfil de loja |
|---|---|---|---|
| Moderno | Inter | Inter | Padrão, seguro |
| Robusto | Barlow Condensed | Inter | Picape / 4x4 / seminovos populares |
| Premium | Playfair Display | Source Sans 3 | Importados / alto padrão |

---

## 7. Estrutura de arquivos

```
CarShopSite/
├── docker-compose.yml               # WordPress + MariaDB + WP-CLI + phpMyAdmin + Mailpit
├── Makefile                         # make up · install · seed · lint · reset
├── docker/
│   ├── setup.sh                     # instalação automática (roda no container)
│   └── uploads.ini
│
├── plugin-lv-estoque/               # o dado — nunca depende do tema
│   ├── lv-estoque.php               # header do plugin, bootstrap
│   ├── inc/
│   │   ├── cpt-veiculo.php          # register_post_type
│   │   ├── taxonomias.php           # register_taxonomy + termos padrão
│   │   ├── campos-metabox.php       # rwmb_meta_boxes
│   │   ├── opcoes-schema.php        # esquema das Configurações do Site
│   │   ├── opcoes-loja.php          # a tela (Settings API)
│   │   ├── query-filtros.php        # pre_get_posts
│   │   ├── schema.php               # JSON-LD Vehicle + AutoDealer + OG
│   │   ├── helpers.php              # lv_preco(), lv_whatsapp_link(), lv_km()
│   │   └── admin-colunas.php        # listagem de estoque no admin
│   ├── assets/
│   │   ├── admin-opcoes.css
│   │   └── admin-opcoes.js          # abas, media picker, repeaters
│   └── seeds/
│       ├── marcas.json              # termos pré-cadastrados
│       ├── veiculos-demo.json       # 6 carros fake para demonstração
│       └── seed.php                 # wp eval-file
│
├── tema-lv/                         # a aparência
│   ├── style.css
│   ├── functions.php
│   ├── theme.json                   # paleta/tipografia do editor
│   ├── front-page.php
│   ├── archive-veiculo.php
│   ├── single-veiculo.php
│   ├── page.php · 404.php · index.php · header.php · footer.php
│   ├── inc/
│   │   ├── customizacao.php         # variáveis CSS
│   │   ├── icones.php               # SVG inline, sem webfont
│   │   ├── enqueue.php
│   │   └── otimizacao.php           # remove emojis, jQuery migrate, etc.
│   ├── template-parts/
│   │   ├── card-veiculo.php
│   │   ├── filtros.php
│   │   ├── galeria.php
│   │   ├── ficha-tecnica.php
│   │   ├── cta-whatsapp.php
│   │   ├── form-contato.php
│   │   └── home/{hero,destaques,diferenciais,sobre,depoimentos,contato}.php
│   └── assets/
│       ├── css/{base,componentes,paginas}.css
│       ├── js/{filtros,galeria,menu}.js
│       └── img/placeholder-carro.svg
│
└── deploy/
    ├── setup-loja.sh                # script WP-CLI de nova instalação
    ├── loja.env.example
    └── checklist.md
```

---

## 8. Código de referência

### 8.1 Registro do CPT

```php
// plugin-lv-estoque/inc/cpt-veiculo.php
add_action( 'init', function () {
    register_post_type( 'veiculo', [
        'labels' => [
            'name'               => 'Veículos',
            'singular_name'      => 'Veículo',
            'add_new_item'       => 'Adicionar veículo',
            'edit_item'          => 'Editar veículo',
            'not_found'          => 'Nenhum veículo cadastrado',
        ],
        'public'        => true,
        'has_archive'   => 'veiculos',
        'rewrite'       => [ 'slug' => 'veiculos', 'with_front' => false ],
        'menu_icon'     => 'dashicons-car',
        'menu_position' => 5,
        'supports'      => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'show_in_rest'  => true,
    ] );
}, 5 );
```

### 8.2 Campos do veículo (Meta Box)

```php
// plugin-lv-estoque/inc/campos-metabox.php
add_filter( 'rwmb_meta_boxes', function ( array $meta_boxes ): array {
    $meta_boxes[] = [
        'id'         => 'lv_veiculo_fotos',
        'title'      => 'Fotos',
        'post_types' => [ 'veiculo' ],
        'fields'     => [
            [
                'id'               => 'galeria',
                'name'             => 'Galeria',
                'type'             => 'image_advanced',
                'desc'             => 'Mínimo 4 fotos, ideal de 8 a 12.',
                'max_file_uploads' => 20,
                'image_size'       => 'lv-thumb',
            ],
        ],
    ];
    return $meta_boxes;
} );
```

### 8.3 Filtros da vitrine

```php
// plugin-lv-estoque/inc/query-filtros.php
add_action( 'pre_get_posts', function ( $q ) {
    if ( is_admin() || ! $q->is_main_query() ) return;
    if ( ! $q->is_post_type_archive( 'veiculo' ) && ! $q->is_tax( [ 'marca', 'carroceria' ] ) ) return;

    $q->set( 'posts_per_page', 12 );

    $meta = [ 'relation' => 'AND' ];

    // Vendidos saem da vitrine, mas a URL continua viva.
    // O NOT EXISTS cobre veículos cadastrados antes do campo existir.
    $meta[] = [
        'relation' => 'OR',
        [ 'key' => 'status_veiculo', 'value' => 'vendido', 'compare' => '!=' ],
        [ 'key' => 'status_veiculo', 'compare' => 'NOT EXISTS' ],
    ];

    if ( null !== ( $v = lv_get_int( 'preco_min' ) ) ) {
        $meta[] = [ 'key' => 'preco', 'value' => $v, 'type' => 'NUMERIC', 'compare' => '>=' ];
    }
    if ( null !== ( $v = lv_get_int( 'preco_max' ) ) ) {
        $meta[] = [ 'key' => 'preco', 'value' => $v, 'type' => 'NUMERIC', 'compare' => '<=' ];
    }
    if ( null !== ( $v = lv_get_int( 'ano_min' ) ) ) {
        $meta[] = [ 'key' => 'ano_modelo', 'value' => $v, 'type' => 'NUMERIC', 'compare' => '>=' ];
    }
    if ( null !== ( $v = lv_get_int( 'km_max' ) ) ) {
        $meta[] = [ 'key' => 'km', 'value' => $v, 'type' => 'NUMERIC', 'compare' => '<=' ];
    }
    if ( count( $meta ) > 1 ) $q->set( 'meta_query', $meta );

    $tax = [];
    foreach ( [ 'marca', 'carroceria', 'cambio', 'combustivel' ] as $t ) {
        if ( $q->is_tax( $t ) || empty( $_GET[ $t ] ) ) continue;
        $tax[] = [
            'taxonomy' => $t,
            'field'    => 'slug',
            'terms'    => array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $t ] ) ),
        ];
    }
    if ( $tax ) { $tax['relation'] = 'AND'; $q->set( 'tax_query', $tax ); }

    switch ( lv_filtro( 'ordem' ) ) {
        case 'preco_asc':  $q->set( 'meta_key', 'preco' ); $q->set( 'orderby', 'meta_value_num' ); $q->set( 'order', 'ASC' );  break;
        case 'preco_desc': $q->set( 'meta_key', 'preco' ); $q->set( 'orderby', 'meta_value_num' ); $q->set( 'order', 'DESC' ); break;
        case 'km_asc':     $q->set( 'meta_key', 'km' );    $q->set( 'orderby', 'meta_value_num' ); $q->set( 'order', 'ASC' );  break;
        default:           $q->set( 'orderby', 'date' );   $q->set( 'order', 'DESC' );
    }
} );
```

### 8.4 Link de WhatsApp (o CTA que gera o dinheiro do cliente)

```php
// plugin-lv-estoque/inc/helpers.php
function lv_whatsapp_link( $post_id = null, string $numero = '', string $mensagem = '' ) {
    $post_id = $post_id ?: get_the_ID();
    $numero  = $numero ? preg_replace( '/\D/', '', $numero ) : lv_whatsapp_numero( $post_id );

    if ( ! $numero ) return '';

    if ( ! $mensagem ) {
        $mensagem = sprintf(
            "Olá! Vi o %s no site e queria mais informações.\n%s",
            get_the_title( $post_id ),
            get_permalink( $post_id )
        );
    }

    return 'https://wa.me/' . $numero . '?text=' . rawurlencode( $mensagem );
}

// Vendedor responsável, com fallback para o primeiro das configurações.
function lv_whatsapp_numero( $post_id = null ): string {
    $numero = (string) lv_field( 'vendedor', $post_id, '' );

    if ( ! $numero ) {
        $vendedores = lv_option( 'vendedores', [] );
        $numero     = (string) ( $vendedores[0]['whatsapp'] ?? '' );
    }

    return preg_replace( '/\D/', '', $numero );
}

function lv_preco( $post_id = null ): string {
    $post_id = $post_id ?: get_the_ID();
    if ( ! lv_field( 'mostrar_preco', $post_id, true ) ) return 'Consulte';
    $p = lv_field( 'preco_promocional', $post_id ) ?: lv_field( 'preco', $post_id );
    return $p ? 'R$ ' . number_format( (float) $p, 0, ',', '.' ) : 'Consulte';
}
```

O link com mensagem pré-preenchida é o detalhe que mais impressiona o dono da loja na demonstração. Mostre isso primeiro na reunião de venda.

### 8.5 JSON-LD (SEO)

```php
// plugin-lv-estoque/inc/schema.php
add_action( 'wp_head', function () {
    if ( ! is_singular( 'veiculo' ) ) return;
    $id = get_the_ID();

    $data = [
        '@context'                 => 'https://schema.org',
        '@type'                    => 'Car',
        'name'                     => get_the_title(),
        'url'                      => get_permalink(),
        'brand'                    => [ '@type' => 'Brand', 'name' => lv_termo( 'marca', $id ) ],
        'vehicleModelDate'         => lv_field( 'ano_modelo', $id ),
        'vehicleTransmission'      => lv_termo( 'cambio', $id ),
        'fuelType'                 => lv_termo( 'combustivel', $id ),
        'mileageFromOdometer'      => [ '@type' => 'QuantitativeValue', 'value' => (int) lv_field( 'km', $id, 0 ), 'unitCode' => 'KMT' ],
        'offers' => [
            '@type'         => 'Offer',
            'price'         => (float) ( lv_field( 'preco_promocional', $id ) ?: lv_field( 'preco', $id, 0 ) ),
            'priceCurrency' => 'BRL',
            'itemCondition' => 'https://schema.org/UsedCondition',
            'availability'  => 'disponivel' === lv_status( $id )
                ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
            'seller' => lv_schema_dealer(),
        ],
    ];

    echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
}, 20 );
```

Na home sai um `AutoDealer` com endereço e horários vindos das opções. É o que faz a loja aparecer na busca local do Google.

O mesmo arquivo emite as **Open Graph tags** — é o que faz o link compartilhado no WhatsApp aparecer com a foto e o preço do carro.

---

## 9. Requisitos não-funcionais

**Performance** (o público chega pelo celular, muitas vezes em 4G ruim):
- LCP < 2,5s no mobile; nota Lighthouse ≥ 85.
- Todas as fotos em WebP, `loading="lazy"` exceto a primeira da galeria (que leva `fetchpriority="high"`).
- Tamanhos registrados: `lv-card` (600×400, crop), `lv-galeria` (1200×800), `lv-thumb` (200×140).
- CSS < 60 KB, JS < 30 KB, sem jQuery no front.
- Sem fonte externa além dos dois pesos escolhidos.

**Acessibilidade:** contraste AA, `alt` gerado automaticamente a partir do título do veículo quando vazio, navegação por teclado na galeria, área de toque ≥ 44px.

**Segurança:** desabilitar edição de arquivos (`DISALLOW_FILE_EDIT`), limitar tentativas de login, `wp_nonce` em formulários, sanitização de todo `$_GET`/`$_POST`, backups diários automáticos.

**LGPD:** aviso de cookies, política de privacidade publicada, formulário com consentimento explícito, dados de lead não armazenados no banco sem necessidade (encaminhe por e-mail/WhatsApp e não guarde).

---

## 10. Processo de implantação de uma nova loja

Meta: **2 a 3 horas** de trabalho técnico após o template estar pronto (fora produção de conteúdo e fotos).

### Fase 0 — Coleta (antes de tocar em código)

Envie ao cliente um formulário único pedindo:
- Logo em PNG/SVG fundo transparente (se não tiver, cobre à parte pra criar).
- Cores da marca (ou aceite que você escolha).
- Endereço completo, telefone fixo, WhatsApp de cada vendedor, horários.
- Instagram/Facebook.
- Texto sobre a loja (ou 5 perguntas que você transforma em texto).
- 3 a 5 fotos da fachada e do showroom.
- Planilha ou lista dos carros do estoque atual.

Sem isso completo, não comece. O maior atraso desse tipo de projeto é esperar conteúdo do cliente.

### Fase 1 — Instalação (30 min)

```bash
# deploy/setup-loja.sh
wp core download --locale=pt_BR
wp config create --dbname="$DB" --dbuser="$USER" --dbpass="$PASS"
wp core install --url="$URL" --title="$LOJA" --admin_user=admin \
  --admin_password="$SENHA" --admin_email="$EMAIL"

wp language core install pt_BR --activate
wp option update timezone_string 'America/Sao_Paulo'
wp rewrite structure '/%postname%/' --hard

# nosso código
git clone <repo-tema>   wp-content/themes/tema-lv
git clone <repo-plugin> wp-content/plugins/plugin-lv-estoque
wp plugin install meta-box --activate
wp theme activate tema-lv
wp plugin activate plugin-lv-estoque
wp plugin install wordfence updraftplus webp-express --activate

# páginas base
wp post create --post_type=page --post_title='Sobre'   --post_status=publish
wp post create --post_type=page --post_title='Contato' --post_status=publish
wp post create --post_type=page --post_title='Política de Privacidade' --post_status=publish

# marcas e termos pré-cadastrados
wp eval 'lv_seed_termos_padrao();'

wp post delete 1 2 --force   # "Olá mundo" e página de exemplo
```

Nenhum zip de licença para providenciar: o `meta-box` vem do repositório do WordPress.

### Fase 2 — Personalização (45 min)

1. Configurações do Site → Identidade: logo, favicon, 3 cores, preset de fonte.
2. Configurações do Site → Contato: endereço, mapa, telefones, horários.
3. Configurações do Site → Vendedores: nome + WhatsApp de cada um.
4. Configurações do Site → Conteúdo: hero, sobre, diferenciais, depoimentos.
5. Menu principal e rodapé.
6. Analytics e Pixel.

### Fase 3 — Estoque (30–60 min, depende da quantidade)

- Cadastre 10 a 15 carros. Marque 6 como destaque.
- Regra de foto: mínimo 4 por carro (frente 3/4, traseira 3/4, interior/painel, porta-malas). Redimensione para 1600px de largura antes de subir.
- Se a loja tem mais de 30 carros, cadastre 15 e ensine o cliente a fazer o resto — isso já é parte do treinamento.

### Fase 4 — Publicação (30 min)

- Domínio apontado, SSL ativo, `www` → não-`www` (ou o inverso, mas escolha um).
- `WP_ENVIRONMENT_TYPE` = `production` (o Analytics e o Pixel só disparam aí).
- Sitemap enviado ao Google Search Console.
- Perfil da Empresa no Google criado/atualizado com o link do site. **Isto vale mais para a loja do que o site em si** — venda os dois juntos.
- Link do site na bio do Instagram.
- Backup automático configurado.
- Teste em celular real: WhatsApp abre? Mensagem vem preenchida? Galeria desliza?

### Fase 5 — Entrega (1 hora)

- Grave um vídeo de 10 minutos (Loom/OBS) mostrando: cadastrar carro, subir fotos, marcar como vendido, editar preço. **Grave uma vez, reaproveite em todos os clientes** — só a parte específica da loja muda.
- PDF de uma página com login, links e os 4 passos de cadastrar carro.
- Combine o que está no suporte e o que é serviço extra.

### Checklist de aceite

- [ ] Site abre em < 3s no 4G
- [ ] Todos os botões de WhatsApp abrem com mensagem preenchida e o número certo
- [ ] Filtros retornam resultado correto e URL é compartilhável
- [ ] Carro marcado como vendido some da vitrine e mantém a URL com selo
- [ ] Nenhum texto "Lorem ipsum" ou dado da loja anterior
- [ ] Formulário de contato entrega e-mail (teste real)
- [ ] Favicon, título da aba e compartilhamento no WhatsApp com imagem correta (OG tags)
- [ ] SSL sem alerta de conteúdo misto
- [ ] Cliente conseguiu cadastrar um carro sozinho na frente de você

---

## 11. Manutenção

**Plano mensal sugerido inclui:** hospedagem, domínio, backup, atualizações de segurança, até X alterações de conteúdo, cadastro de até Y carros/mês.

**Rotina mensal (30 min por site):** atualizar core/plugins em ambiente de teste primeiro, verificar backup restaurável, olhar Search Console, checar carros marcados como vendidos há mais de 60 dias (arquivar).

**Atualizando o template em todas as lojas:** como tema e plugin estão em Git, o fluxo é `git pull` em cada site (ou um deploy via script/Ansible). Por isso nada de personalização direta no código do tema por cliente — se um cliente precisa de algo único, isso vai num arquivo `custom.css`/`custom.php` isolado, nunca editando o core do template.

**Campo novo nas Configurações do Site:** uma entrada em `inc/opcoes-schema.php` e pronto — a tela, a sanitização e o valor padrão saem sozinhos. Sem migração de banco, porque tudo vive num único option.

**Sem licenças para renovar.** Nenhum plugin do template é pago, então não existe o cenário de um site sair do ar porque a licença de alguém venceu.

---

## 12. Roadmap

**v1.1**
- Importação de estoque via CSV (o cliente já costuma ter uma planilha).
- Comparador de até 3 veículos.
- "Avise-me quando chegar um carro assim" (captura de lead).

**v1.2**
- Simulador de financiamento com tabela de juros configurável (hoje a taxa está fixa no JS).
- Feed XML de saída para OLX/Webmotors.
- Página de "Vendemos seu carro" com formulário de avaliação.

**v2**
- Multi-loja (filiais).
- Dashboard simples: carros mais vistos, cliques em WhatsApp por veículo.
- Versão do template para motos.
