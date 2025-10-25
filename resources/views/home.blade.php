<?php
declare(strict_types=1);

use App\Models\User;

/** @var User $user */
$user ??= auth()->user();
?>
@extends('layouts.plain')

@section('title', 'Welcome - AspireCloud')

@section('content')
    <main class="homepage">
        <div class="message">
            <p>
                Welcome to the AspireCloud API, a service of <a href="https://www.aspirepress.org">AspirePress</a>.
            </p>
            <p>
                Right now, we're currently in beta testing, but anyone can join.
                During the beta, you'll need an auth token to access the API: you can get one by registering using
                the link below, then selecting "API Tokens" from the top-right menu.
            </p>
            <hr>
            <p>Useful links:
                <a href="https://github.com/aspirepress/aspirecloud" target="_blank">Github</a>
                | <a href="https://codex.wordpress.org/WordPress.org_API" target="_blank">WordPress API Reference</a>
            </p>
        </div>

        <nav>
            <ul>
                @if ($user)
                    <li><a href="{{route('dashboard')}}">Dashboard</a></li>
                    <li>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        >
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                @else
                    <li><a href="{{ route('login') }}">Login</a></li>
                    <li><a href="{{ route('register') }}">Register</a></li>
                @endif
            </ul>
        </nav>

        @if (!app()->isProduction())
            <div class="warning message">
                <p>
                    <strong>Warning:</strong>
                    This is a development server, and the stability of the platform is not guaranteed. It may be reset
                    from time to time without warning, requiring you to re-register and generate new API keys.
                </p>
                <p>
                    Email <em>cannot</em> be sent by this server, and thus password resets can only be performed by a site administrator.
                    Warnings about verifying your account email can be ignored for the time being.
                </p>
            </div>
        @endif
    </main>
@endsection

@section('head')
    <style>
        main.homepage {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 66vh;

            font-family: sans-serif;
            font-size: 1.5rem;
        }

        main.homepage .message {
            width: 66%;

            border: 1px solid lightgrey;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 1em;
            border-radius: 8px;
            background-color: white;
        }

        main.homepage nav {
            width: 66%;
            font-size: 3rem;
        }

        main.homepage nav ul {
            list-style: none;
            display: flex;
            flex-direction: row;
            padding: 0;
            justify-content: space-evenly;
        }

        main.homepage a {
            text-decoration: none;
            color: darkcyan;
        }

        main.homepage a:hover {
            color: deepskyblue;
        }

        main.homepage .warning {
            background-color: lightyellow;
        }
    </style>
@endsection
