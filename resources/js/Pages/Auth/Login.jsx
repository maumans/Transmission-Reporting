import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button, TextField, Box, Typography, Paper } from '@mui/material';
import { Link } from '@inertiajs/react';

export default function Login({ status, canResetPassword }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <Box
            sx={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                minHeight: '100vh',
                bgcolor: 'background.default',
            }}
        >
            <Head title="Connexion" />
            
            <Paper
                elevation={3}
                sx={{
                    p: 4,
                    width: '100%',
                    maxWidth: 400,
                }}
            >
                <Typography component="h1" variant="h5" align="center" gutterBottom>
                    Connexion
                </Typography>

                {status && (
                    <Typography color="error" align="center" sx={{ mb: 2 }}>
                        {status}
                    </Typography>
                )}

                <form onSubmit={submit}>
                    <TextField
                        margin="normal"
                        required
                        fullWidth
                        id="email"
                        label="Email"
                        name="email"
                        autoComplete="email"
                        autoFocus
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={!!errors.email}
                        helperText={errors.email}
                    />

                    <TextField
                        margin="normal"
                        required
                        fullWidth
                        name="password"
                        label="Mot de passe"
                        type="password"
                        id="password"
                        autoComplete="current-password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        error={!!errors.password}
                        helperText={errors.password}
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        sx={{ mt: 3, mb: 2 }}
                        disabled={processing}
                    >
                        Se connecter
                    </Button>

                    {canResetPassword && (
                        <Box sx={{ textAlign: 'center' }}>
                            <Link
                                href={route('password.request')}
                                style={{ textDecoration: 'none' }}
                            >
                                Mot de passe oublié ?
                            </Link>
                        </Box>
                    )}
                </form>
            </Paper>
        </Box>
    );
}
