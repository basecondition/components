<?php
/**
 * User: joachimdorr
 * Date: 27.05.20
 * Time: 08:25
 */

namespace BSC\Controller;


use BSC\Exception\InvalidArgumentException;
use BSC\Exception\NotFoundException;
use BSC\Model\Clang;
use BSC\Service\ArticleHelper;
use Psr\Http\Message\ServerRequestInterface;
use rex_article;
use rex_article_content;
use rex_clang;
use rex_request;

class Article extends AbstractController
{
    public static function getArticleAction(ServerRequestInterface $request, array $args = [])
    {
        try {
            $clangId = rex_request::get('clang', 'integer', rex_clang::getCurrentId());
            $clang = rex_clang::get($clangId);
            $articleId = $args['route_parameter']['id'];

            $article = rex_article::get($articleId, $clangId);

            if (is_null($article) || !$article->isOnline()) {
                throw new NotFoundException('Article is was not found', 'not_found');
            }

            $clangResponse = new Clang();
            $clangResponse->setId($clangId)
                ->setName($clang->getName())
                ->setDefault((rex_clang::getCurrentId() === $clang->getId()))
                ->getCode($clang->getCode());

            $articleContent = new rex_article_content($articleId, $clangId);
            $articleResponse = new \BSC\Model\Article();
            $articleResponse->setId($articleId)
                ->setContent(self::replaceLinks($articleContent->getArticle()))
                ->setName($article->getName())
                ->setClang($clangResponse);

            return self::response($articleResponse, 200);

        } catch (InvalidArgumentException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 400);
        } catch (NotFoundException $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => $e->getKey(), 'error_description' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            //self::getLogger()::logException($e);
            return self::response(['error' => 'internal_error', 'error_description' => $e->getMessage()], 500);
        }
    }

    protected static function replaceLinks($content)
    {
        return preg_replace_callback(
            '@redaxo://(\d+)(?:-(\d+))?/?@i',
            function ($matches) {
                return ArticleHelper::getUrl($matches[1]);
                //return '#article-'.$matches[1];
                //return rex_getUrl($matches[1], isset($matches[2]) ? $matches[2] : (int) $this->clang);
            },
            $content
        );
    }

}
