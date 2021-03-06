<template>
    <div id="home">
        <div class="panel panel-default">
            <div class="panel-heading">Account</div>
            <div class="panel-body">
                <div class="form-inline">
                    <div class="form-group">
                        <label class="sr-only" for="username">Username</label>
                        <input type="username" class="form-control" id="username" placeholder="Username" v-model="username">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="password">Password</label>
                        <input type="password" class="form-control" id="password" placeholder="Password" v-model="password">
                    </div>
                    <div class="form-group">
                        <label class="sr-only" for="auth_method">Auth Method</label>
                        <select class="form-control" id="auth_method" name="auth_method" v-model="auth_method">
                            <option value="ptc">Pokemon Trainer Club</option>
                            <option value="google">Google Account</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-default" v-on:click="show" id="show">Show</button>
                </div>
            </div>
        </div>

        <div class="alert alert-info" v-if="pokemons.length <= 0">
            <strong>Oops!</strong>
            Please enter account to load pokemon data.
        </div>

        <div class="table-responsive" v-if="pokemons.length > 0">
            <table class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Pokemon</th>
                        <th>Fast</th>
                        <th>Special</th>
                        <th>Level</th>
                        <th>CP</th>
                        <th>HP</th>
                        <th class="attack">ATK</th>
                        <th class="defense">DEF</th>
                        <th class="stamina">STA</th>
                        <th v-on:click="sortBy('piv')">IV%</th>
                        <th v-on:click="sortBy('pcp')">CP%</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="pokemon in pokemons | orderBy 'perfectIV' -1">
                        <td>
                            <div class="name pull-left">
                                <img v-bind:src="pokemon.pokemon_id | pokemonIcon">
                                {{ (pokemon.nickname || pokemon.name || '') | formatName }}
                            </div>
                        </td>
                        <td>
                            <span class="pull-left">{{ pokemon.move1Name | formatName }}</span>
                            <label class="label label-default pull-right visible-md-block visible-lg-block">{{ pokemon.move1 }}</label>
                        </td>
                        <td>
                            <span class="pull-left">{{ pokemon.move2Name | formatName }}</span>
                            <label class="label label-default pull-right visible-md-block visible-lg-block">{{ pokemon.move2 }}</label>
                        </td>
                        <td>{{ pokemon.level }}</td>
                        <td>{{ pokemon.cp }}</td>
                        <td>{{ pokemon.hp_max }}</td>
                        <td class="attack">{{ pokemon.attack }}</td>
                        <td class="defense">{{ pokemon.defense }}</td>
                        <td class="stamina">{{ pokemon.stamina }}</td>
                        <td>{{ pokemon.perfectIV | formatPercentage }}</td>
                        <td>{{ pokemon.perfectCP | formatPercentage }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style>
.name {
    display: inline;
}

th, td {
    text-align: center;
}

/* th */
th.attack {
    color: #FFFFFF;
    background-color: #8F1D21;
}

th.defense {
    color: #FFFFFF;
    background-color: #003171;
}

th.stamina {
    color: #FFFFFF;
    background-color: #006442;
}

/* td */
td.attack {
    color: #FFFFFF;
    background-color: #DC3023;
}

td.defense {
    color: #FFFFFF;
    background-color: #1F4788;
}

td.stamina {
    color: #FFFFFF;
    background-color: #26A65B;
}

/* overwrite */
.table-striped > tbody > tr:nth-of-type(odd) {
    background-color: #fbfbfb;
}
</style>

<script>
import api from '../api'
import LevelToCPM from '../data/level-to-cpm.json'
import PokemonData from '../data/pokemon-data.json'
import Moves from '../data/moves.json'

export default {

    data() {
        return {
            username   : "",
            password   : "",
            auth_method: "ptc",
            pokemons   : [],
        }
    },

    methods: {
        show() {
            if (this.username === "") {
                this.alertError("Please enter username")
            }else if (this.password === "") {
                this.alertError("Please enter password")
            }else if ($.inArray(this.auth_method, ['ptc', 'google']) === false) {
                this.alertError("Please select your account auth method")
            }else{
                var showButton = jQuery("button#show")

                showButton.html("Loading...")
                showButton.prop("disabled", true)

                var moves = this.generateMovesList()

                api.pokemon.all({
                    username   : this.username,
                    password   : this.password,
                    auth_method: this.auth_method,
                }).then(
                    response => {
                        let data     = response.data
                        let pokemons = data.pokemons

                        for(let i=0; i<pokemons.length; i++) {
                            let pokemon = pokemons[i]

                            pokemons[i].name      = this.findPokemonById(pokemon.pokemon_id).name
                            pokemons[i].level     = this.determineLevel(pokemon.cp_multiplier)
                            pokemons[i].perfectIV = this.determinePerfectIV(pokemon.attack, pokemon.defense, pokemon.stamina)
                            pokemons[i].perfectCP = this.determinePerfectCP(pokemon.pokemon_id, pokemon.attack, pokemon.defense, pokemon.stamina)

                            pokemons[i].move1Name = moves[pokemon.move1].Name
                            pokemons[i].move2Name = moves[pokemon.move2].Name
                        }

                        this.pokemons = pokemons

                        showButton.html("Show")
                        showButton.prop("disabled", false)
                    },
                    response => {
                        console.log(response)

                        this.alertError('Unknow error!')

                        showButton.html("Show")
                        showButton.prop("disabled", false)
                    }
                )
            }
        },

        sortBy(name) {
            switch(name) {
                case 'piv':
                    this.pokemons.sort((a, b) => {
                        let perfectIV1 = a.perfectIV
                        let perfectIV2 = b.perfectIV

                        if (perfectIV1 < perfectIV2) return 1
                        if (perfectIV1 > perfectIV2) return -1

                        return 0
                    })
                    break
                case 'pcp':
                    this.pokemons.sort((a, b) => {
                        let perfectCP1 = a.perfectCP
                        let perfectCP2 = b.perfectCP

                        if (perfectCP1 < perfectCP2) return 1
                        if (perfectCP1 > perfectCP2) return -1

                        return 0
                    })
                    break
            }
        },

        findPokemonById(pokemonId) {
            let pokemon

            for(let i=0; i<PokemonData.length; i++) {
                if (PokemonData[i].id == pokemonId) {
                    pokemon = PokemonData[i]
                    break
                }
            }

            return pokemon
        },

        alertError(message) {
            swal("Error!", message, "error")
        },

        determineLevel(cpm) {
            let ret = 0

            for(let level in LevelToCPM) {
                if (Math.abs(cpm - LevelToCPM[level]) < 0.0001) {
                    ret = parseFloat(level)
                }
            }

            return ret
        },

        determinePerfectIV(attack, defense, stamina) {
            return (attack + defense + stamina) / 45
        },

        determinePerfectCP(pokemonId, individualAttack, individualDefense, individualStamina) {
            let pokemon = this.findPokemonById(pokemonId)

            let baseAttack  = pokemon.stats.attack
            let baseDefense = pokemon.stats.defense
            let baseStamina = pokemon.stats.stamina

            let perfectCp = (baseAttack + 15) * Math.pow((baseDefense + 15), 0.5) * Math.pow((baseStamina + 15), 0.5) * (Math.pow(LevelToCPM['40'], 2) / 10)
            let maxCp     = (baseAttack + individualAttack) * Math.pow((baseDefense + individualDefense), 0.5) * Math.pow((baseStamina + individualStamina), 0.5) * (Math.pow(LevelToCPM['40'], 2) / 10)

            return maxCp / perfectCp
        },

        generateMovesList() {
            let moveList = [];

            for(let i=0; i<Moves.length; i++) {
                let moves = Moves[i];

                moveList[moves.Id] = moves;
            }

            return moveList;
        }
    }

}
</script>
