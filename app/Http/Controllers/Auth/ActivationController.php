<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ActivationRequest;
use App\User;

class ActivationController extends Controller
{
	public function __invoke(ActivationRequest $request)
	{
		$user = $this->credentials($request);

		return $this->attempActivation($user);
	}

	private function credentials(): object
	{
		$user = User::whereEmail(request()->email)
			->whereActivationToken(request()->token)
			->first();

		if (!$user) {
			return User::whereEmail(request()->email)->whereActivationToken(null)->first();
		}

		return $user;
	}

	private function attempActivation($user)
	{
		try {
			if ($user->activation_token !== null && !$user->active) {
				$user->update([
					'activation_token' => null,
					'active' => true
				]);

				auth()->login($user);

				return redirect()->route('home')->withSuccess(
					"Welcome $user->name, Your account successfully activation."
				);
			}

			if ($user->active && $user->activation_token === null) {
				return redirect()->route('login')->withFailed(
					'Your account already activate, please login.'
				);
			}
		} catch (\Exception $e) {
			return redirect()->route('login')->withFailed(
				'Invalid token, try again'
			);
		}
	}
}
