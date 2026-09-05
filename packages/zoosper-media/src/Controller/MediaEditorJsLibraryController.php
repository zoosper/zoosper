<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Media\EditorJs\MediaPickerReadQuery;
use Zoosper\Media\EditorJs\MediaPickerReadRepository;
use Zoosper\Media\EditorJs\MediaPickerResponder;

/** Authenticated Admin adapter for selecting existing Media images in Editor.js. */
final readonly class MediaEditorJsLibraryController
{
    public function __construct(
        private MediaPickerReadRepository $media,
        private MediaPickerResponder $responses,
    ) {
    }

    public function index(Request $request): Response
    {
        return $this->responses->respond(
            $this->media->paginate(MediaPickerReadQuery::fromRequest($request))
        );
    }
}
