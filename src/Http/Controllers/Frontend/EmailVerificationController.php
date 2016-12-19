<?php

/*
 * NOTICE OF LICENSE
 *
 * Part of the Rinvex Fort Package.
 *
 * This source file is subject to The MIT License (MIT)
 * that is bundled with this package in the LICENSE file.
 *
 * Package: Rinvex Fort Package
 * License: The MIT License (MIT)
 * Link:    https://rinvex.com
 */

namespace Rinvex\Fort\Http\Controllers\Frontend;

use Rinvex\Fort\Http\Controllers\AbstractController;
use Rinvex\Fort\Contracts\EmailVerificationBrokerContract;
use Rinvex\Fort\Http\Requests\Frontend\EmailVerificationRequest;

class EmailVerificationController extends AbstractController
{
    /**
     * Show the email verification request form.
     *
     * @return \Illuminate\Http\Response
     */
    public function request()
    {
        return view('rinvex/fort::frontend/verification.email.request');
    }

    /**
     * Process the email verification request form.
     *
     * @param \Rinvex\Fort\Http\Requests\Frontend\EmailVerificationRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function send(EmailVerificationRequest $request)
    {
        $result = app('rinvex.fort.emailverification')
            ->broker($this->getBroker())
            ->send($request->except('_token'));

        switch ($result) {
            case EmailVerificationBrokerContract::LINK_SENT:
                return intend([
                    'url'  => '/',
                    'with' => ['rinvex.fort.alert.success' => trans($result)],
                ]);

            default:
                return intend([
                    'back'       => true,
                    'withInput'  => $request->only('email'),
                    'withErrors' => ['email' => trans($result)],
                ]);
        }
    }

    /**
     * Process the email verification.
     *
     * @param \Rinvex\Fort\Http\Requests\Frontend\EmailVerificationRequest $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function verify(EmailVerificationRequest $request)
    {
        $result = app('rinvex.fort.emailverification')->broker($this->getBroker())->verify($request->except('_token'));

        switch ($result) {
            case EmailVerificationBrokerContract::EMAIL_VERIFIED:
                return intend([
                    'url'  => '/',
                    'with' => ['rinvex.fort.alert.success' => trans($result)],
                ]);

            case EmailVerificationBrokerContract::INVALID_USER:
            case EmailVerificationBrokerContract::INVALID_TOKEN:
            default:
                return intend([
                    'route'      => 'rinvex.fort.frontend.verification.email.request',
                    'withInput'  => $request->only('email'),
                    'withErrors' => ['token' => trans($result)],
                ]);
        }
    }
}
