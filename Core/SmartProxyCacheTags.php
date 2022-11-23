<?php
declare(strict_types=1);

namespace foun10\SmartProxy\Core;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Application\Model\Category;
use OxidEsales\Eshop\Application\Model\Content;

class SmartProxyCacheTags
{
    const CACHE_TAG_HTML = 'html';
    const CACHE_TAG_DETAILS = 'details';
    const CACHE_TAG_ALIST = 'alist';
    const CACHE_TAG_CONTENT = 'content';
    const CACHE_TAG_SEARCH = 'search';

    const CACHE_TAGS = [
        self::CACHE_TAG_HTML,
        self::CACHE_TAG_DETAILS,
        self::CACHE_TAG_ALIST,
        self::CACHE_TAG_CONTENT,
        self::CACHE_TAG_SEARCH,
    ];

    public function getCacheTagsForController(FrontendController $controller): array
    {
        $tags = [self::CACHE_TAG_HTML];
        $className = $controller->getClassKey();

        switch ($className) {
            case 'details':
                $tags[] = self::CACHE_TAG_DETAILS;

                /** @var Article $product */
                $product = $controller->getProduct();

                if ($product && $product->getId()) {
                    $tags = array_merge($tags, $this->getCacheTagsForDetailsProduct($product));
                }

                break;
            case 'alist':
                $tags[] = self::CACHE_TAG_ALIST;

                /** @var Category $category */
                $category = $controller->getActiveCategory();

                if ($category && $category->getId()) {
                    $tags = array_merge($tags, $this->getCacheTagsForCategory($category));
                }

                break;
            case 'content':
                $tags[] = self::CACHE_TAG_CONTENT;

                /** @var Content $content */
                $content = $controller->getContent();

                if ($content && $content->getId()) {
                    $tags = array_merge($tags, $this->getCacheTagsForContent($content));
                }

                break;
            default:
                $tags[] = $className;
                break;
        }

        if (method_exists($controller, 'getSmartproxyCacheTags')) {
            $tags = array_merge($tags, $controller->getSmartproxyCacheTags());
        }

        return $tags;
    }

    public function getCacheTagsForDetailsProduct(Article $product): array
    {
        return [self::CACHE_TAG_DETAILS . '-' . $product->getParentId() ?: $product->getId()];
    }

    public function getCacheTagsForCategory(Category $category): array
    {
        return [self::CACHE_TAG_ALIST . '-' . $category->getId()];
    }

    public function getCacheTagsForContent(Content $content): array
    {
        return [self::CACHE_TAG_CONTENT . '-' . ($content->getLoadId() ?: $content->getId())];
    }
}
