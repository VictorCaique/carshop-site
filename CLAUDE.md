# CLAUDE.md

Contexto para sessões do Claude Code neste repositório.
Leia também `README.md` (como rodar) e `docs/SPEC.md` (a especificação do produto).

## O que é isto

Template WordPress replicável para lojas de veículos de bairro: **1 tema + 1 plugin próprios**,
versionados, instalados por script em cada cliente novo. O produto não é um site — é um template
mais um processo de implantação. O que se otimiza aqui é o tempo da **segunda loja em diante**.

Consequência prática: quase toda decisão se resolve perguntando *"isso continua funcionando
igual em 10 lojas, atualizadas por `git pull`?"*. Personalização por cliente vive nas
Configurações do Site, nunca no código.

## Comandos

```bash
make up        # sobe os containers (Docker precisa estar rodando)
make install   # instala o WordPress, o Meta Box, ativa tema e plugin
make seed      # 6 veículos de demonstração com fotos geradas via GD
make lint      # php -l em todo o tema e plugin
make wp CMD="plugin list"
make reset     # APAGA banco e core e reinstala do zero
```

Site em :8080 (`admin`/`admin`), phpMyAdmin em :8081, Mailpit em :8025.
Tema e plugin são bind mounts — editar o arquivo reflete no próximo reload, sem rebuild.

## Invariantes

Coisas que quebram o produto se forem violadas, não só o gosto de quem escreveu:

1. **O dado mora no plugin, a aparência no tema.** CPT, taxonomias e campos ficam em
   `plugin-lv-estoque/`. Se o tema for trocado um dia, o estoque do cliente não pode sumir.

2. **O tema nunca lê post meta direto.** Sempre `lv_field()` / `lv_option()`, de
   `plugin-lv-estoque/inc/helpers.php`. É isso que torna barato trocar a camada de campos.

3. **O tema NUNCA declara uma função com nome do plugin** — nem com `function_exists`.
   Já custou um fatal error: ao ativar o plugin pelo painel, o WordPress carrega o tema
   *antes* de incluir o arquivo do plugin, então os stubs do tema existiam primeiro e o plugin
   morria com `Cannot redeclare`. Se o plugin sumir, o tema desvia o front para
   `tema-lv/inc/sem-plugin.php` (503) via `template_include` — sem declarar nada.

4. **Nenhuma cor literal no CSS**, nem os neutros. Tudo vem dos tokens em
   `tema-lv/inc/customizacao.php`, alimentados pelas Configurações do Site. Existe
   `--cor-branco` e `--cor-inverso` justamente para não escrever `#fff`. Trocar 3 cores
   + logo tem que dar uma loja nova.

5. **Todo token novo existe nos dois esquemas, claro e escuro.** Os tokens saem em três
   camadas: `lv_tokens_base()` (marca, fonte, geometria — igual nos dois), `lv_tokens_esquema()`
   (neutros e derivados da primária — uma versão por esquema) e os blocos `[data-tema]`,
   emitidos só quando alguém pode trocar de tema. Se um token de cor entrar só em
   `lv_tokens_base()`, ele fica errado em metade dos sites.

   Daí a separação de papéis que parece redundante e não é:
   `--cor-titulo` é *texto* (vira claro no escuro), `--cor-secundaria` é *superfície escura
   da marca* (rodapé, hero — escura sempre). `--cor-primaria-contraste` e
   `--cor-destaque-contraste` são calculados por luminância: é o que impede texto branco
   em cima de amarelo.

6. **Sem jQuery no front, sem page builder, sem plugin pago.** Orçamentos: CSS < 60 KB,
   JS < 30 KB (hoje 29 KB e 9,1 KB). No admin o jQuery é permitido — é do próprio WP.

7. **Filtros da vitrine por GET, via `pre_get_posts`.** Sem FacetWP/Search&Filter: a URL
   precisa ser compartilhável e indexável.

8. **Veículo vendido não é apagado.** Sai da vitrine, mas a URL continua viva com selo e
   CTA de similares. Preserva SEO e ainda gera lead.

## Camada de campos

O SPEC original pedia ACF PRO; foi trocado pela alternativa gratuita, em duas partes:

- **Campos do veículo → Meta Box** (`meta-box`, gratuito, wordpress.org), registrados em PHP
  em `inc/campos-metabox.php` via `rwmb_meta_boxes`. A galeria usa `image_advanced`.
- **Configurações do Site → tela nossa**, em `inc/opcoes-loja.php`, com a Settings API.
  Options page é a única peça que o Meta Box cobra (MB Settings Page / AIO).

`inc/opcoes-schema.php` é a **fonte única da verdade** dessa tela: renderização, sanitização e
valores padrão saem todos dele. Campo novo nas configurações = uma entrada nesse array, mais nada.
Tudo grava num único option, `lv_opcoes` — não há migração de banco para fazer.

## Armadilhas já pagas

Não reintroduza:

- **`image_advanced` grava um ID por linha**, todas com a mesma meta key. Ler com
  `get_post_meta( $id, 'galeria', false )`, nunca com `$single = true`. O `lv_galeria_ids()`
  já normaliza isso e põe a imagem destacada na frente.
- **Checkbox precisa do `<input type="hidden" value="0">` antes**, senão desmarcar não envia
  nada e o valor antigo permanece.
- **Repeater precisa reindexar os `name` após adicionar, remover ou reordenar.** Sem isso os
  índices ficam com buracos (`[0]`, `[2]`) e o PHP perde linhas. Ver `assets/admin-opcoes.js`.
- **A ordem do repeater de vendedores é significativa:** o primeiro é o padrão para veículos
  sem vendedor definido. Por isso existe o arrastar-para-reordenar.
- **`status_veiculo` no `meta_query` precisa do `NOT EXISTS`** em `OR` com o `!= 'vendido'`,
  senão veículos cadastrados antes do campo existir somem da vitrine.
- **`--header-altura` tem que bater com a altura real da barra.** A gaveta do menu mobile é
  posicionada com `inset: var(--header-altura) 0 auto 0`, então um logo mais alto que o token
  faz o menu abrir por cima do cabeçalho. Por isso a altura da barra é derivada
  (`max(altura_topbar, altura_logo + 20)`) em vez de ser o campo cru.
- **Nada de `esc_attr()` dentro de `<style>`.** O navegador não decodifica entidades ali:
  `esc_attr("'Inter', sans-serif")` vira `&#039;Inter&#039;` literal e a fonte não carrega.
  Os tokens saem por `lv_css_valor()`, que é lista branca de caracteres.
- **O `<script id="lv-tema-inicial">` é inline e síncrono de propósito.** Ele lê o
  `localStorage` e aplica `[data-tema]` antes da primeira pintura; deferido, o visitante
  do tema escuro leva um flash branco em toda navegação.
- **A ordem dos blocos importa** no `<style id="lv-tokens">`: `:root:not([data-tema="claro"])`
  dentro da media query e `:root[data-tema="escuro"]` têm a *mesma* especificidade, então a
  escolha do visitante só vence a do aparelho porque vem depois no arquivo.
- **TinyMCE dentro de aba escondida** pode abrir com altura zero. Há um `mceRepaint` na troca
  de aba; se o editor do "Sobre" aparecer quebrado, é por aí.

## Estado atual

O ambiente **já roda** em `localhost:8080`, com uma loja de verdade configurada (logo, mapa,
vendedores) e os 6 veículos do seed. Home, vitrine com filtros, ficha do veículo, 404 e rodapé
foram vistos nos dois esquemas de cor.

A aba **Aparência** (fundo claro/escuro/automático, botão de tema, cantos, sombra, largura,
overlay do hero) foi conferida em tela e o contraste dos tokens é verificado por cálculo: todo
par texto/fundo passa em AA nos dois esquemas. Se mexer nos tokens, refaça essa conta em vez
de confiar no olho — a cor da loja entra na fórmula, então o que passa numa loja pode falhar
na próxima.

O que **não** foi exercitado ainda: o alternador de tema num aparelho com `prefers-color-scheme:
dark` de verdade (só forçado no desktop), o `image_advanced` com muitas fotos, e o envio do
formulário de contato pelo Mailpit.

## Convenções

- Prefixo `lv_` em funções PHP, `lv-` em classes CSS e IDs de assets.
- Indentação com tab (WordPress Coding Standards).
- Comentários em português, explicando **por que**, não o que o código já diz.
- Identificadores e comentários sem acentuação, por segurança de encoding.
- **Dívida conhecida:** as strings de interface também estão sem acento
  ("Configuracoes do Site", "Veiculo"). Se for corrigir, corrija todas de uma vez —
  meio caminho fica pior que nenhum.

## Pendências

1. Rodar o ambiente pela primeira vez (acima).
2. O link "Desenvolvido por" em `tema-lv/footer.php` ainda aponta para `example.com`.
   É a melhor fonte de leads do projeto — vale apontar para o lugar certo.
3. Decidir se tema e plugin viram **dois repositórios separados**. O `deploy/setup-loja.sh`
   já espera clonar cada um por site, que é o que permite o `git pull` para atualizar N lojas.
   Hoje é um repo só.
4. Lighthouse mobile contra a meta do SPEC §9: LCP < 2,5s, nota ≥ 85.
5. A taxa do simulador de financiamento está fixa no JS (1,79% a.m.). Virar campo das
   configurações está no roadmap v1.2.
