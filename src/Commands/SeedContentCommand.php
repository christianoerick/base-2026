<?php
namespace ChristianoErick\Base\Commands;

use Exception;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use ChristianoErick\Base\Models\Post;
use ChristianoErick\Base\Models\Page;
use ChristianoErick\Base\Models\Category;
use ChristianoErick\Base\Models\Domain;
use ChristianoErick\Base\Models\Image;
use ChristianoErick\Base\Models\Audio;
use ChristianoErick\Base\Models\File;
use ChristianoErick\Base\Models\Tag;

class SeedContentCommand extends Command
{
	protected $signature = 'admin:seed
							{type : Tipo de conteúdo (site|portal)}
							{--count=20 : Quantidade de posts/páginas}
							{--domains=2 : Quantidade de domínios}
							{--with-media : Gerar imagens, áudios e arquivos}
							{--with-tags : Gerar e vincular tags}';

	protected $description = 'Gera conteúdo inteligente em português com relacionamentos completos';

	public function handle()
	{
		$type = strtolower($this->argument('type'));

		if (!in_array($type, ['site', 'portal'])) {
			$this->error("❌ Tipo inválido! Use: site ou portal");
			return Command::FAILURE;
		}

		$count = (int) $this->option('count');
		$domainsCount = (int) $this->option('domains');
		$withMedia = $this->option('with-media');
		$withTags = $this->option('with-tags');

		$this->info("🚀 Gerando conteúdo para: " . strtoupper($type));
		$this->newLine();

		//DB::beginTransaction();

		try {
			// 1. Criar domínios
			$domains = $this->createDomains($domainsCount);

			// 2. Criar tags se solicitado
			$tags = $withTags ? $this->createTags() : collect();

			// 3. Criar mídia se solicitado
			$images = $withMedia ? $this->createImages() : collect();
			$audios = $withMedia ? $this->createAudios($type) : collect();
			$files = $withMedia ? $this->createFiles() : collect();

			// 4. Gerar conteúdo baseado no tipo
			match($type) {
				'site' => $this->generateSiteContent($domains),
				'portal' => $this->generatePortalContent($count, $domains, $tags, $images, $files),
				'blog' => $this->generateBlogContent($count, $domains, $tags, $images),
				'tv' => $this->generateTvContent($count, $domains, $tags, $images, $audios),
			};

			//DB::commit();

			$this->newLine();
			$this->info('✅ Conteúdo gerado com sucesso!');
			$this->displaySummary($type, $count, $domains, $tags, $images, $audios, $files);

			return Command::SUCCESS;

		} catch (Exception $e) {
			//DB::rollBack();
			$this->error("❌ Erro ao gerar conteúdo: " . $e->getMessage());
			return Command::FAILURE;
		}
	}

	protected function createDomains($count)
	{
		$this->task('Criando domínios', function () use ($count, &$domains) {
			$domains = collect();

			foreach (range(1, $count) as $i) {
				$domains->push(Domain::create([
					'status' => true,
					'name' => "Domínio {$i}",
					'domain' => "dominio-{$i}.ddev.site",
				]));
			}

			return $domains;
		});

		return $domains;
	}

	protected function createTags()
	{
		$this->task('Criando tags', function (&$tags) {
			$tags = collect();

			foreach ($this->tags as $tagName) {
				$tags->push(Tag::firstOrCreate(
					['slug' => Str::slug($tagName)],
					['name' => $tagName]
				));
			}

			return $tags;
		});

		return $tags;
	}

	protected function createImages()
	{
		$this->task('Criando imagens', function (&$images) {
			$images = collect();
			foreach ($this->images as $image)
			{

			}

			for ($i = 1; $i <= 30; $i++) {
				$images->push(Image::create([
					'filename' => "imagem-{$i}.jpg",
					'path' => "/storage/images/imagem-{$i}.jpg",
					'alt' => "Imagem ilustrativa {$i}",
					'caption' => "Legenda da imagem {$i}",
					'size' => rand(50000, 500000)
				]));
			}

			return $images;
		});

		return $images;
	}

	protected function createAudios($type)
	{
		$this->task('Criando áudios', function () use ($type, &$audios) {
			$audios = collect();
			$audioTypes = $type === 'tv' ? ['entrevista', 'programa', 'podcast'] : ['podcast', 'audio'];

			for ($i = 1; $i <= 10; $i++) {
				$audioType = $audioTypes[array_rand($audioTypes)];
				$audios->push(Audio::create([
					'filename' => "{$audioType}-{$i}.mp3",
					'path' => "/storage/audios/{$audioType}-{$i}.mp3",
					'title' => ucfirst($audioType) . " #{$i}",
					'duration' => rand(300, 3600),
					'size' => rand(1000000, 10000000)
				]));
			}

			return $audios;
		});

		return $audios;
	}

	protected function createFiles()
	{
		$this->task('Criando arquivos', function (&$files) {
			$files = collect();
			$fileTypes = ['pdf', 'doc', 'xlsx', 'zip'];

			for ($i = 1; $i <= 15; $i++) {
				$ext = $fileTypes[array_rand($fileTypes)];
				$files->push(File::create([
					'filename' => "documento-{$i}.{$ext}",
					'path' => "/storage/files/documento-{$i}.{$ext}",
					'title' => "Documento {$i}",
					'mime_type' => $this->getMimeType($ext),
					'size' => rand(10000, 1000000)
				]));
			}

			return $files;
		});

		return $files;
	}

	protected function generateSiteContent($domains)
	{
		$pages = [
			['title' => 'Início', 'content' => 'Bem-vindo ao nosso site institucional.'],
			['title' => 'Sobre Nós', 'content' => 'Conheça nossa história e valores.'],
			['title' => 'Serviços', 'content' => 'Veja todos os serviços que oferecemos.'],
			['title' => 'Produtos', 'content' => 'Confira nosso catálogo de produtos.'],
			['title' => 'Contato', 'content' => 'Entre em contato conosco.'],
			['title' => 'Política de Privacidade', 'content' => 'Nossa política de privacidade.'],
		];

		$bar = $this->output->createProgressBar(count($pages));
		$bar->start();

		foreach ($pages as $pageData) {
			$page = Page::create([
				'title' => $pageData['title'],
				'slug' => Str::slug($pageData['title']),
				'content' => $this->generateContent($pageData['content'], 500),
				'is_active' => true,
				'published_at' => now()
			]);

			// Vincular a domínios
			$page->domains()->attach($domains->random(rand(1, $domains->count()))->pluck('id'));

			$bar->advance();
		}

		$bar->finish();
		$this->newLine();
	}

	protected function generatePortalContent($count, $domains, $tags, $images, $files)
	{
		$categories = $this->createCategories($this->newsCategories, 'noticias');

		$bar = $this->output->createProgressBar($count);
		$bar->start();

		for ($i = 0; $i < $count; $i++) {
			$category = $categories->random();
			$title = str_replace('{categoria}', strtolower($category->name), $this->newsTitles[array_rand($this->newsTitles)]);

			$post = Post::create([
				'title' => $title,
				'slug' => Str::slug($title) . '-' . uniqid(),
				'excerpt' => $this->generateExcerpt(),
				'content' => $this->generateContent('notícia', rand(800, 2000)),
				'type' => 'noticias',
				'status' => 'published',
				'published_at' => now()->subDays(rand(0, 30)),
				'author' => $this->getRandomAuthor()
			]);

			$this->attachRelations($post, $categories->random(rand(1, 3)), $domains, $tags, $images, $files, null);

			$bar->advance();
		}

		$bar->finish();
		$this->newLine();
	}

	protected function generateBlogContent($count, $domains, $tags, $images)
	{
		$categories = $this->createCategories($this->blogCategories, 'blog');

		$bar = $this->output->createProgressBar($count);
		$bar->start();

		for ($i = 0; $i < $count; $i++) {
			$category = $categories->random();
			$title = str_replace('{categoria}', strtolower($category->name), $this->blogTitles[array_rand($this->blogTitles)]);

			$post = Post::create([
				'title' => $title,
				'slug' => Str::slug($title) . '-' . uniqid(),
				'excerpt' => $this->generateExcerpt(),
				'content' => $this->generateContent('blog', rand(1000, 3000)),
				'type' => 'blog',
				'status' => 'published',
				'published_at' => now()->subDays(rand(0, 60)),
				'author' => $this->getRandomAuthor()
			]);

			$this->attachRelations($post, $categories->random(rand(1, 2)), $domains, $tags, $images, null, null);

			$bar->advance();
		}

		$bar->finish();
		$this->newLine();
	}

	protected function generateTvContent($count, $domains, $tags, $images, $audios)
	{
		$categories = $this->createCategories($this->tvCategories, 'tv');

		$bar = $this->output->createProgressBar($count);
		$bar->start();

		for ($i = 0; $i < $count; $i++) {
			$category = $categories->random();

			$post = Post::create([
				'title' => "Programa de {$category->name} - Episódio " . rand(1, 100),
				'slug' => Str::slug($category->name . ' episodio ' . $i),
				'excerpt' => $this->generateExcerpt(),
				'content' => $this->generateContent('programa de TV', rand(500, 1500)),
				'type' => 'tv',
				'status' => 'published',
				'published_at' => now()->subDays(rand(0, 90)),
				'author' => $this->getRandomAuthor()
			]);

			$this->attachRelations($post, $categories->random(rand(1, 2)), $domains, $tags, $images, null, $audios);

			$bar->advance();
		}

		$bar->finish();
		$this->newLine();
	}

	protected function createCategories($categoryNames, $type)
	{
		$categories = collect();

		foreach ($categoryNames as $name) {
			$category = Category::firstOrCreate([
				'slug' => Str::slug($name),
				'type' => $type
			], [
				'name' => $name,
				'description' => "Categoria de {$name}"
			]);

			$categories->push($category);
		}

		return $categories;
	}

	protected function attachRelations($post, $categories, $domains, $tags, $images, $files, $audios)
	{
		// Categorias
		$post->categories()->attach($categories->pluck('id'));

		// Domínios
		if ($domains->isNotEmpty()) {
			$post->domains()->attach($domains->random(rand(1, min(2, $domains->count())))->pluck('id'));
		}

		// Tags
		if ($tags->isNotEmpty()) {
			$post->tags()->attach($tags->random(rand(3, 7))->pluck('id'));
		}

		// Imagens
		if ($images->isNotEmpty()) {
			$post->images()->attach($images->random(rand(1, 5))->pluck('id'));
		}

		// Arquivos
		if ($files && $files->isNotEmpty() && rand(0, 1)) {
			$post->files()->attach($files->random(rand(1, 2))->pluck('id'));
		}

		// Áudios
		if ($audios && $audios->isNotEmpty() && rand(0, 1)) {
			$post->audios()->attach($audios->random()->id);
		}
	}

	protected function generateExcerpt()
	{
		$excerpts = [
			'Este conteúdo traz informações relevantes e atualizadas sobre o tema.',
			'Descubra insights importantes e análises detalhadas neste artigo.',
			'Uma análise completa e atualizada sobre o assunto.',
			'Confira os detalhes e entenda melhor esta questão.',
			'Tudo o que você precisa saber sobre este tema importante.'
		];

		return $excerpts[array_rand($excerpts)];
	}

	protected function generateContent($context, $words = 1000)
	{
		$paragraphs = [];
		$paragraphCount = ceil($words / 100);

		for ($i = 0; $i < $paragraphCount; $i++) {
			$paragraphs[] = "Este é um parágrafo de exemplo gerado automaticamente para {$context}. " .
						   "O conteúdo aqui apresentado serve como placeholder e deve ser substituído por texto real. " .
						   "Em um cenário de produção, este texto seria gerado dinamicamente ou obtido de uma fonte de dados apropriada. " .
						   "É importante manter a qualidade e relevância do conteúdo para garantir uma boa experiência do usuário.";
		}

		return implode("\n\n", $paragraphs);
	}

	protected function getRandomAuthor()
	{
		$authors = [
			'João Silva', 'Maria Santos', 'Pedro Oliveira', 'Ana Costa',
			'Carlos Ferreira', 'Juliana Lima', 'Rafael Souza', 'Fernanda Alves'
		];

		return $authors[array_rand($authors)];
	}

	protected function getMimeType($extension)
	{
		return match($extension) {
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'zip' => 'application/zip',
			default => 'application/octet-stream'
		};
	}

	protected function displaySummary($type, $count, $domains, $tags, $images, $audios, $files)
	{
		$this->table(
			['Item', 'Quantidade'],
			[
				['Tipo de Conteúdo', strtoupper($type)],
				['Posts/Páginas', $count],
				['Domínios', $domains->count()],
				['Tags', $tags->count()],
				['Imagens', $images->count()],
				['Áudios', $audios->count()],
				['Arquivos', $files->count()],
			]
		);
	}



	protected $newsCategories = [
		'Política',
		'Economia',
		'Tecnologia',
		'Esportes',
		'Entretenimento',
		'Cultura',
		'Saúde',
		'Educação',
		'Ciência',
		'Meio Ambiente',
	];

	protected $blogCategories = [
		'Lifestyle',
		'Viagens',
		'Gastronomia',
		'Moda',
		'Decoração',
		'Finanças Pessoais',
		'Produtividade',
		'Desenvolvimento Pessoal',
	];

	protected $tvCategories = [
		'Jornalismo',
		'Entretenimento',
		'Documentários',
		'Séries',
		'Entrevistas',
		'Debates',
		'Programas Infantis',
	];

	protected $newsTitles = [
		'Nova lei aprovada no Congresso pode mudar {categoria}',
		'Especialistas debatem futuro de {categoria} no Brasil',
		'Governo anuncia investimentos bilionários em {categoria}',
		'Pesquisa revela tendências alarmantes em {categoria}',
		'Brasil avança 5 posições no ranking global de {categoria}',
		'Crise em {categoria}: Entenda os impactos no seu bolso',
		'Startup brasileira revoluciona o mercado de {categoria}',
		'O que esperar de {categoria} para o próximo ano?',
		'Entrevista exclusiva: Ministro fala sobre {categoria}',
		'Gigantes da tecnologia apostam tudo em {categoria}',
		'Protestos marcam votação sobre regras de {categoria}',
		'Dólar alto impacta diretamente o setor de {categoria}',
		'Novo estudo de Harvard derruba mitos sobre {categoria}',
		'Fusão de empresas promete agitar o mundo de {categoria}',
		'Escândalo em {categoria} gera repercussão internacional',
		'Evento em São Paulo reúne líderes de {categoria}',
		'Como a Inteligência Artificial está transformando {categoria}',
		'Relatório aponta crescimento recorde em {categoria}',
		'Setor de {categoria} abre milhares de vagas de emprego',
		'Anvisa aprova novas normas para {categoria}',
		'Sustentabilidade vira prioridade em empresas de {categoria}',
		'Fraudes em {categoria} causam prejuízo milionário',
		'Mercado de {categoria} reage positivamente à nova medida',
		'Bolsa de Valores: Ações de {categoria} disparam',
		'China e EUA disputam hegemonia em {categoria}',
		'Pequenos negócios de {categoria} ganham incentivo fiscal',
		'Consumidores reclamam de alta nos preços de {categoria}',
		'O fim de uma era? Mudanças drásticas em {categoria}',
		'Documentário polêmico expõe bastidores de {categoria}',
		'Avanço científico promete mudar a história de {categoria}',
		'Legislativo discute urgência em pauta de {categoria}',
		'Regiões Norte e Nordeste lideram expansão em {categoria}',
		'União Europeia impõe barreiras para {categoria} brasileira',
		'Startups de {categoria} atraem investidores anjo',
		'Hacker vaza dados sigilosos sobre {categoria}',
		'Eleições podem definir o destino de {categoria}',
		'Clima extremo afeta produção e serviços de {categoria}',
		'Google anuncia nova ferramenta voltada para {categoria}',
		'Brasileiros gastam mais com {categoria} em 2024',
		'Especialista alerta para bolha no mercado de {categoria}',
		'Histórico: Mulher assume liderança global em {categoria}',
		'Prefeitura lança programa de fomento à {categoria}',
		'Justiça suspende liminar que afetava {categoria}',
		'Exportações de {categoria} batem recorde histórico',
		'Falência de gigante de {categoria} choca o mercado',
		'Nova variante impacta retomada de {categoria}',
		'Conferência da ONU traz diretrizes para {categoria}',
		'Aplicativo de {categoria} vira febre nacional',
		'Investigação aponta cartel no setor de {categoria}',
		'O impacto do 5G no desenvolvimento de {categoria}',
	];

	protected $blogTitles = [
		'Como melhorar sua {categoria}: 10 dicas práticas',
		'O guia definitivo de {categoria} para iniciantes',
		'Minha experiência real com {categoria}: Vale a pena?',
		'5 erros comuns em {categoria} (e como evitá-los)',
		'Transforme sua visão sobre {categoria} em 30 dias',
		'Por que ninguém te contou isso sobre {categoria}?',
		'O segredo para dominar {categoria} de uma vez por todas',
		'Checklist: Tudo o que você precisa para começar em {categoria}',
		'As 7 melhores ferramentas para quem ama {categoria}',
		'Mitos e Verdades sobre {categoria} que você precisa saber',
		'{categoria}: Um hobby ou uma profissão?',
		'Como economizar dinheiro investindo em {categoria}',
		'Passo a passo: Dominando {categoria} do zero',
		'Entenda a psicologia por trás de {categoria}',
		'3 livros essenciais para entender {categoria}',
		'O que aprendi após 5 anos trabalhando com {categoria}',
		'Como explicar {categoria} para seus avós',
		'A história não contada de {categoria}',
		'{categoria} para preguiçosos: O caminho mais fácil',
		'Faça você mesmo: Projetos incríveis de {categoria}',
		'As tendências de {categoria} que vão bombar este ano',
		'Estudo de caso: Como fulano venceu em {categoria}',
		'Pare de perder tempo com {categoria} da forma errada',
		'O futuro de {categoria}: Minhas previsões',
		'Quiz: O quanto você realmente sabe sobre {categoria}?',
		'Como monetizar seu conhecimento em {categoria}',
		'A relação surpreendente entre {categoria} e felicidade',
		'Desafio de 7 dias: Focando em {categoria}',
		'Por que {categoria} é mais importante do que você pensa',
		'Os 10 mandamentos de quem pratica {categoria}',
		'Review honesto: O melhor curso de {categoria}',
		'Como balancear vida pessoal e {categoria}',
		'Ideias criativas para inovar em {categoria}',
		'O glossário completo de termos de {categoria}',
		'Depoimentos inspiradores sobre {categoria}',
		'Como convencer seu chefe a investir em {categoria}',
		'{categoria} no home office: Dicas de produtividade',
		'O lado sombrio de {categoria} que ninguém mostra',
		'Comparativo: Método A vs Método B em {categoria}',
		'Infográfico: A evolução de {categoria} no tempo',
		'Como criar uma rotina focada em {categoria}',
		'Os maiores influenciadores de {categoria} para seguir',
		'Podcast: Batendo um papo sobre {categoria}',
		'Resumo da semana: O que rolou em {categoria}',
		'Tutorial avançado de {categoria} para experts',
		'Como superar o bloqueio criativo em {categoria}',
		'Dicas de segurança para quem atua em {categoria}',
		'A ciência explica: Os benefícios de {categoria}',
		'Minimalismo e {categoria}: Como unir os dois?',
		'Carta aberta aos amantes de {categoria}',
	];

	protected $tags = [
		'Tutorial',
		'Guia Completo',
		'Dicas Práticas',
		'Análise',
		'Opinião',
		'Entrevista',
		'Reportagem Especial',
		'Bastidores',
		'Ao Vivo',
		'Infográfico',
		'Podcast',
		'Vídeo',
		'Resenha',
		'Estudo de Caso',
		'Passo a Passo',
		'Breaking News',
		'Exclusivo',
		'Últimas Notícias',
		'Em Alta',
		'Tendência',
		'Novidade',
		'Destaque',
		'Plantão',
		'Urgente',
		'Atualização',
		'Brasil',
		'Mundo',
		'Política',
		'Economia',
		'Tecnologia',
		'Inovação',
		'Ciência',
		'Saúde',
		'Educação',
		'Cultura',
		'Esportes',
		'Entretenimento',
		'Meio Ambiente',
		'Justiça',
		'Segurança Pública',
		'Mercado Financeiro',
		'Marketing Digital',
		'Carreira',
		'Empreendedorismo',
		'Startups',
		'Investimentos',
		'Gestão',
		'Liderança',
		'Produtividade',
		'Bem-estar',
	];

	protected $images = [
		'001.jpg',
		'002.jpg',
		'003.jpg',
		'004.jpg',
		'005.jpg',
		'006.jpg',
		'007.jpg',
		'008.jpg',
		'009.jpg',
		'010.avif',
		'011.avif',
		'012.avif',
		'013.jpg',
		'014.jpg',
		'015.jpg',
		'016.jpg',
		'017.jpg',
		'018.jpg',
		'019.avif',
		'020.avif',
		'021.avif',
		'022.jpg',
		'023.jpg',
		'024.jpg',
		'025.jpg',
		'026.avif',
		'027.jpg',
		'028.jpg',
		'029.avif',
		'030.jpg',
		'031.avif',
		'032.avif',
		'033.avif',
		'034.jpg',
		'035.jpg',
		'036.jpg',
		'037.jpg',
		'038.jpg',
		'039.jpg',
		'040.jpg',
		'041.jpg',
		'042.jpg',
		'043.avif',
		'044.jpg',
		'045.avif',
		'046.avif',
		'047.jpg',
		'048.jpg',
		'049.jpg',
		'050.jpg',
		'051.jpg',
		'052.avif',
		'053.jpg',
		'054.jpg',
		'055.jpg',
		'056.jpg',
		'057.jpg',
		'058.jpg',
		'059.jpg',
		'060.jpg',
		'061.jpg',
		'062.jpg',
		'063.avif',
		'064.jpg',
		'065.avif',
		'066.jpg',
		'067.jpg',
		'068.jpg',
		'069.jpg',
		'070.jpg',
		'071.jpg',
		'072.avif',
		'073.jpg',
		'074.avif',
		'075.avif',
		'076.jpg',
		'077.avif',
		'078.jpg',
		'079.jpg',
		'080.jpg',
		'081.avif',
		'082.avif',
		'083.avif',
		'084.jpg',
		'085.jpg',
		'086.jpg',
		'087.jpg',
		'088.avif',
		'089.jpg',
		'090.avif',
		'091.jpg',
		'092.avif',
		'092.jpg',
		'093.jpg',
		'094.jpg',
		'095.jpg',
		'096.avif',
		'097.jpg',
		'099.jpg',
		'100.jpg',
	];
}
