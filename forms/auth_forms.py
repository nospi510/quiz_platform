from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, SubmitField
from wtforms.validators import DataRequired, Length, Regexp, EqualTo, ValidationError
from models.user import User

class RegistrationForm(FlaskForm):
    username = StringField('Nom d’utilisateur', validators=[DataRequired(), Length(min=4, max=50)])
    password = PasswordField('Mot de passe', validators=[DataRequired(), Length(min=6)])
    confirm_password = PasswordField('Confirmer le mot de passe', validators=[DataRequired(), EqualTo('password')])
    extension = StringField('Numéro d’extension (5000-5099)', validators=[
        DataRequired(),
        Regexp('^500[0-9]$', message='L’extension doit être un numéro entre 5000 et 5099.')
    ])
    submit = SubmitField('S’inscrire')

    def validate_extension(self, field):
        if User.query.filter_by(extension=field.data).first():
            raise ValidationError('Ce numéro d’extension est déjà pris.')

class LoginForm(FlaskForm):
    username = StringField('Nom d’utilisateur', validators=[DataRequired()])
    password = PasswordField('Mot de passe', validators=[DataRequired()])
    submit = SubmitField('Se connecter')