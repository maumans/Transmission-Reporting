import React from 'react';
import { Head, useForm } from '@inertiajs/react';
import { Button, TextField, Box, Typography, Paper } from '@mui/material';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('password.email'));
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
            <Head title="Mot de passe oublié" />

            <Paper
                elevation={3}
                sx={{
                    p: 4,
                    width: '100%',
                    maxWidth: 400,
                }}
            >
                <Typography component="h1" variant="h5" align="center" gutterBottom>
                    Mot de passe oublié
                </Typography>

                <Typography variant="body2" align="center" sx={{ mb: 3 }}>
                    Entrez votre adresse email pour recevoir un lien de réinitialisation.
                </Typography>

                {status && (
                    <Typography color="success" align="center" sx={{ mb: 2 }}>
                        {status}
                    </Typography>
                )}

                <form onSubmit={submit}>
                    <TextField
                        margin="normal"
                        required
                        fullWidth
                        id="email"
                        label="Adresse email"
                        name="email"
                        autoComplete="email"
                        autoFocus
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        error={!!errors.email}
                        helperText={errors.email}
                    />

                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        sx={{ mt: 3, mb: 2 }}
                        disabled={processing}
                    >
                        Envoyer le lien
                    </Button>
                </form>
            </Paper>
        </Box>
    );
}
