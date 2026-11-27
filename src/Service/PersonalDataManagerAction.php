<?php

declare(strict_types=1);

namespace WEM\PersonalDataManagerBundle\Service;

use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\ContaoCorePermissions;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use WEM\PersonalDataManagerBundle\Exception\AccessDeniedException;

/**
 * Handles the AJAX requests sent by the Personal Data Manager UI, in the back end
 * as well as in the front end module.
 */
class PersonalDataManagerAction
{
    public const string BACKEND_MODULE = 'wem-personal-data-manager';

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly PersonalDataManager $manager,
        private readonly Security $security,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly ContaoCsrfTokenManager $csrfTokenManager,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod(Request::METHOD_POST)
            && $request->request->has('TL_WEM_AJAX')
            && 'be_pdm' === $request->request->get('wem_module');
    }

    /**
     * Process an AJAX request and return the response (JSON or file download).
     */
    public function processAjaxRequest(Request $request): Response
    {
        $returnHttpCode = Response::HTTP_OK;

        try {
            $arrResponse = match ($request->request->get('action')) {
                'anonymize_single_personal_data' => $this->anonymizeSinglePersonalData($request),
                'anonymize_personal_data_item' => $this->anonymizeSingleItem($request),
                'anonymize_all_personal_data' => $this->anonymizeAllPersonalData($request),
                'export_single' => $this->exportSingleItem($request),
                'export_all' => $this->exportAllPersonalData($request),
                'show_personal_data_item' => $this->showSingleItem($request),
                'show_file_single_personal_data' => $this->showFileSinglePersonalData($request),
                'download_file_single_personal_data' => $this->downloadFileSinglePersonalData($request),
                default => throw new \InvalidArgumentException('Unknown route'),
            };
        } catch (AccessDeniedException $e) {
            $returnHttpCode = Response::HTTP_UNAUTHORIZED;
            $arrResponse = ['status' => 'error', 'msg' => $e->getMessage()];
        } catch (\Exception $e) {
            $returnHttpCode = Response::HTTP_BAD_REQUEST;
            $arrResponse = ['status' => 'error', 'msg' => $e->getMessage()];
        }

        if ($arrResponse instanceof Response) {
            return $arrResponse;
        }

        // Add Request Token to JSON answer and return
        $arrResponse['rt'] = $this->csrfTokenManager->getDefaultTokenValue();

        return new JsonResponse($arrResponse, $returnHttpCode);
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function anonymizeSinglePersonalData(Request $request): array
    {
        $this->checkMandatoryParameters($request, ['pid', 'ptable', 'email', 'field']);
        $this->checkAccess($request);

        return [
            'status' => 'success',
            'msg' => '',
            'value' => $this->manager->anonymizeByPidAndPtableAndEmailAndField(
                $request->request->getInt('pid'),
                $request->request->getString('ptable'),
                $request->request->getString('email'),
                $request->request->getString('field'),
            ),
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function anonymizeSingleItem(Request $request): array
    {
        $this->checkMandatoryParameters($request, ['pid', 'ptable', 'email']);
        $this->checkAccess($request);

        return [
            'status' => 'success',
            'msg' => '',
            'values' => $this->manager->anonymizeByPidAndPtableAndEmail(
                $request->request->getInt('pid'),
                $request->request->getString('ptable'),
                $request->request->getString('email'),
            ),
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function anonymizeAllPersonalData(Request $request): array
    {
        $this->checkMandatoryParameters($request, ['email']);
        $this->checkAccess($request);

        return [
            'status' => 'success',
            'msg' => '',
            'values' => $this->manager->anonymizeByEmail($request->request->getString('email')),
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function exportSingleItem(Request $request): Response
    {
        $this->checkMandatoryParameters($request, ['pid', 'ptable', 'email']);
        $this->checkAccess($request);

        $zipPath = $this->manager->exportByPidAndPtableAndEmail(
            $request->request->getInt('pid'),
            $request->request->getString('ptable'),
            $request->request->getString('email'),
        );

        return $this->createZipResponse($zipPath, $this->trans('WEM.PEDAMA.CSV.filenameSingleItem') . '.zip');
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function exportAllPersonalData(Request $request): Response
    {
        $this->checkMandatoryParameters($request, ['email']);
        $this->checkAccess($request);

        $zipPath = $this->manager->exportByEmail($request->request->getString('email'));

        return $this->createZipResponse($zipPath, $this->trans('WEM.PEDAMA.CSV.filenameAll') . '.zip');
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function showSingleItem(Request $request): array
    {
        $this->checkMandatoryParameters($request, ['pid', 'ptable', 'email']);
        $this->checkAccess($request);

        $href = $this->manager->getHrefByPidAndPtableAndEmail(
            $request->request->getInt('pid'),
            $request->request->getString('ptable'),
            $request->request->getString('email'),
        );

        if ('' === $href) {
            throw new \Exception($this->trans('WEM.PEDAMA.DEFAULT.noUrlProvided'));
        }

        return [
            'status' => 'success',
            'msg' => '',
            'href' => $href,
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function showFileSinglePersonalData(Request $request): array
    {
        $this->checkMandatoryParameters($request, ['pid', 'ptable', 'email', 'field']);
        $this->checkAccess($request);

        $objFile = $this->manager->getFileByPidAndPtableAndEmailAndField(
            $request->request->getInt('pid'),
            $request->request->getString('ptable'),
            $request->request->getString('email'),
            $request->request->getString('field'),
        );

        return [
            'status' => 'success',
            'msg' => '',
            'content' => \sprintf('data:%s;base64,%s', $objFile->mime, base64_encode($objFile->getContent())),
            'name' => $objFile->name,
        ];
    }

    /**
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function downloadFileSinglePersonalData(Request $request): Response
    {
        $this->checkMandatoryParameters($request, ['pid', 'ptable', 'email', 'field']);
        $this->checkAccess($request);

        $objFile = $this->manager->getFileByPidAndPtableAndEmailAndField(
            $request->request->getInt('pid'),
            $request->request->getString('ptable'),
            $request->request->getString('email'),
            $request->request->getString('field'),
        );

        $response = new Response($objFile->getContent(), Response::HTTP_OK, [
            'Content-Type' => $objFile->mime,
            // Read by the UI script to name the downloaded file
            'filename' => $objFile->name,
        ]);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $objFile->name, 'file'));

        return $response;
    }

    /**
     * In the back end, users allowed to access the module can manage any personal
     * data. In the front end, a valid email + token couple is required.
     *
     * @throws AccessDeniedException
     * @throws \Exception
     */
    protected function checkAccess(Request $request): void
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            if (!$this->security->isGranted(ContaoCorePermissions::USER_CAN_ACCESS_MODULE, self::BACKEND_MODULE)) {
                throw new AccessDeniedException($this->trans('WEM.PEDAMA.DEFAULT.accessDenied'));
            }

            return;
        }

        $token = (string) $this->manager->getTokenInSession();

        if ('' === $token || !$this->manager->isEmailTokenCoupleValid($request->request->getString('email'), $token)) {
            $this->manager->clearTokenInSession();

            throw new AccessDeniedException($this->trans('WEM.PEDAMA.DEFAULT.accessDenied'));
        }

        $this->manager->updateTokenExpiration($token);
    }

    private function checkMandatoryParameters(Request $request, array $parameters): void
    {
        foreach ($parameters as $parameter) {
            if (empty($request->request->get($parameter))) {
                throw new \InvalidArgumentException($this->trans('WEM.PEDAMA.DEFAULT.' . $parameter . 'Empty'));
            }
        }
    }

    private function createZipResponse(string $zipPath, string $filename): BinaryFileResponse
    {
        $response = new BinaryFileResponse($zipPath, Response::HTTP_OK, [
            'Content-Type' => 'application/zip',
            // Read by the UI script to name the downloaded file
            'filename' => $filename,
        ]);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename, 'export.zip');
        $response->deleteFileAfterSend();

        return $response;
    }

    private function trans(string $id): string
    {
        return $this->translator->trans($id, [], 'contao_default');
    }
}
