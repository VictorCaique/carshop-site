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

5. **Sem jQuery no front, sem page builder, sem plugin pago.** Orçamentos: CSS < 60 KB,
   JS < 30 KB (hoje 27 KB e 7,7 KB). No admin o jQuery é permitido — é do próprio WP.

6. **Filtros da vitrine por GET, via `pre_get_posts`.** Sem FacetWP/Search&Filter: a URL
   precisa ser compartilhável e indexável.

7. **Veículo vendido não é apagado.** Sai da vitrine, mas a URL continua viva com selo e
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
- **TinyMCE dentro de aba escondida** pode abrir com altura zero. Há um `mceRepaint` na troca
  de aba; se o editor do "Sobre" aparecer quebrado, é por aí.

## Estado atual

Três commits. **O ambiente nunca foi executado** — o código passou por `php -l` e `node --check`,
mais uma checagem estática de colisão de nomes entre tema e plugin, mas nada rodou de verdade.

O primeiro `make install && make seed` é o teste real. O que eu olharia primeiro, em ordem:
o repeater de vendedores e o media picker nas Configurações do Site, a galeria na ficha do
veículo, e os filtros da vitrine com `?marca=honda&preco_max=80000`.

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
